<?php

namespace App\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class UserController extends Controller
{
    public function index(Request $request, Response $response): Response
    {
        $users = User::with('roles')->get()->map(function (User $user) {
            return [
                'id' => $user->id,
                'fullName' => $user->name,
                'email' => $user->email,
                'status' => $user->deleted_at ? 'inactive' : 'active',
                'roles' => $user->roles->map(function (Role $role) {
                    return [
                        'id' => $role->id,
                        'name' => $role->name,
                        'permissions' => $role->permissions->map(function ($perm) {
                            return [
                                'id' => $perm->id,
                                'name' => $perm->name,
                                'key' => $perm->key,
                            ];
                        })->all(),
                        'createdAt' => $role->created_at ? $role->created_at->toDateTimeString() : null,
                        'updatedAt' => $role->updated_at ? $role->updated_at->toDateTimeString() : null,
                    ];
                }),
                'createdAt' => $user->created_at ? $user->created_at->toDateTimeString() : null,
                'updatedAt' => $user->updated_at ? $user->updated_at->toDateTimeString() : null,
            ];
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
                            ];
                        })->all(),
                'createdAt' => $role->created_at ? $role->created_at->toDateTimeString() : null,
                'updatedAt' => $role->updated_at ? $role->updated_at->toDateTimeString() : null,
            ];
        })->values()->all();

        return $this->json($response, $roles);
    }

    public function hasPermission(string $permission): bool
    {
        return $this->roles()->with('permissions')->get()
            ->flatMap(fn ($role) => $role->permissions)
            ->contains(fn ($perm) => $perm->key === $permission || $perm->key === '*');
    }
}
