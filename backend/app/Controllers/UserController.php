<?php

namespace App\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Eloquent\Collection;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class UserController extends Controller
{
    public function index(Request $request, Response $response): Response
    {
        $users = User::with('roles')->get()->map(function (User $user) {
            return $this->transformUser($user);
        });

        return $this->json($response, $users->all());
    }

    public function roles(Request $request, Response $response): Response
    {
        $roles = Role::all()->map(function (Role $role) {
            return [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $role->permissions->map(function ($perm) {
                    return [
                                'id' => $perm->id,
                                'name' => $perm->name,
                                'key' => $perm->key,
                                'description' => null,
                            ];
                        })->all(),
                'createdAt' => $role->created_at ? $role->created_at->toDateTimeString() : null,
                'updatedAt' => $role->updated_at ? $role->updated_at->toDateTimeString() : null,
            ];
        })->values()->all();

        return $this->json($response, $roles);
    }

    public function store(Request $request, Response $response): Response
    {
        $data = (array) $request->getParsedBody();

        $fullName = trim($data['fullName'] ?? '');
        $email = trim($data['email'] ?? '');

        if ($fullName === '' || $email === '') {
            return $this->json($response, ['message' => 'fullName and email are required'], 422);
        }

        if (User::where('email', $email)->exists()) {
            return $this->json($response, ['message' => 'Email already in use'], 422);
        }

        $password = $data['password'] ?? '';
        if ($password === '') {
            $password = 'test123456';
        }

        $status = $data['status'] ?? 'active';
        $roleIds = is_array($data['roles'] ?? null) ? $data['roles'] : [];

        $user = new User();
        $user->name = $fullName;
        $user->email = $email;
        $user->password = password_hash($password, PASSWORD_BCRYPT);
        $user->save();

        if (!empty($roleIds)) {
            $validRoleIds = Role::whereIn('id', $roleIds)->pluck('id')->all();
            $user->roles()->sync($validRoleIds);
        }

        if ($status === 'inactive') {
            $user->delete();
        }

        $user->load('roles');

        return $this->json($response, $this->transformUser($user), 201);
    }

    public function show(Request $request, Response $response, array $args): Response
    {
        $user = User::withTrashed()->with('roles')->find($args['id'] ?? null);
        if (!$user) {
            return $this->json($response, ['message' => 'User not found'], 404);
        }

        return $this->json($response, $this->transformUser($user));
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $user = User::withTrashed()->with('roles')->find($args['id'] ?? null);
        if (!$user) {
            return $this->json($response, ['message' => 'User not found'], 404);
        }

        $data = (array) $request->getParsedBody();

        if (isset($data['fullName'])) {
            $user->name = trim((string) $data['fullName']);
        }

        if (isset($data['email'])) {
            $email = trim((string) $data['email']);
            if ($email !== '' && User::where('email', $email)->where('id', '!=', $user->id)->exists()) {
                return $this->json($response, ['message' => 'Email already in use'], 422);
            }
            if ($email !== '') {
                $user->email = $email;
            }
        }

        if (!empty($data['password'] ?? '')) {
            $user->password = password_hash((string) $data['password'], PASSWORD_BCRYPT);
        }

        if (isset($data['roles']) && is_array($data['roles'])) {
            $validRoleIds = Role::whereIn('id', $data['roles'])->pluck('id')->all();
            $user->roles()->sync($validRoleIds);
        }

        if (isset($data['status'])) {
            $status = $data['status'] === 'inactive' ? 'inactive' : 'active';
            if ($status === 'inactive' && $user->deleted_at === null) {
                $user->delete();
            } elseif ($status === 'active' && $user->deleted_at !== null) {
                $user->restore();
            }
        }

        $user->save();
        $user->load('roles');

        return $this->json($response, $this->transformUser($user));
    }

    public function destroy(Request $request, Response $response, array $args): Response
    {
        $user = User::withTrashed()->find($args['id'] ?? null);
        if (!$user) {
            return $this->json($response, ['message' => 'User not found'], 404);
        }

        $user->delete();

        return $this->json($response, ['message' => 'User deleted']);
    }

    public function toggleStatus(Request $request, Response $response, array $args): Response
    {
        $user = User::withTrashed()->with('roles')->find($args['id'] ?? null);
        if (!$user) {
            return $this->json($response, ['message' => 'User not found'], 404);
        }

        if ($user->deleted_at) {
            $user->restore();
        } else {
            $user->delete();
        }

        $user->refresh()->load('roles');

        return $this->json($response, $this->transformUser($user));
    }

    public function updateRolePermissions(Request $request, Response $response, array $args): Response
    {
        $role = Role::find($args['id'] ?? null);
        if (!$role) {
            return $this->json($response, ['message' => 'Role not found'], 404);
        }

        $data = (array) $request->getParsedBody();
        $permissionsInput = isset($data['permissions']) && is_array($data['permissions'])
            ? $data['permissions']
            : [];

        $enabledIds = collect($permissionsInput)
            ->filter(fn ($item) => !empty($item['enabled']))
            ->pluck('id')
            ->all();

        if (!empty($enabledIds)) {
            $validPermissionIds = Permission::whereIn('id', $enabledIds)->pluck('id')->all();
        } else {
            $validPermissionIds = [];
        }

        $role->permissions()->sync($validPermissionIds);
        $role->load('permissions');

        $result = [
            'id' => $role->id,
            'name' => $role->name,
            'permissions' => $role->permissions->map(function (Permission $perm) {
                return [
                    'id' => $perm->id,
                    'name' => $perm->name,
                    'key' => $perm->key,
                    'description' => null,
                ];
            })->values()->all(),
        ];

        return $this->json($response, $result);
    }

    private function transformUser(User $user): array
    {
        $roles = $user->relationLoaded('roles') ? $user->roles : new Collection();

        return [
            'id' => $user->id,
            'fullName' => $user->name,
            'email' => $user->email,
            'status' => $user->deleted_at ? 'inactive' : 'active',
            'roles' => $roles->pluck('name')->values()->all(),
            'createdAt' => $user->created_at ? $user->created_at->toDateTimeString() : null,
            'updatedAt' => $user->updated_at ? $user->updated_at->toDateTimeString() : null,
        ];
    }
}
