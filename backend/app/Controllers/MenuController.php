<?php

namespace App\Controllers;

use App\Models\MenuItem;
use App\Models\MenuPermission;
use App\Models\Role;
use Illuminate\Database\Eloquent\Collection;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class MenuController extends Controller
{
    public function index(Request $request, Response $response): Response
    {
        $menuItems = MenuItem::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $hierarchicalMenu = $this->buildHierarchicalMenu($menuItems);

        return $this->json($response, $hierarchicalMenu);
    }

    public function getPermissions(Request $request, Response $response, array $args): Response
    {
        $roleId = $args['id'] ?? null;
        
        $role = Role::find($roleId);
        if (!$role) {
            return $this->json($response, ['message' => 'Role not found'], 404);
        }

        $menuItems = MenuItem::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $hierarchicalMenu = $this->buildHierarchicalMenu($menuItems, $role);

        return $this->json($response, $hierarchicalMenu);
    }

    public function updatePermissions(Request $request, Response $response, array $args): Response
    {
        $roleId = $args['id'] ?? null;
        
        $role = Role::find($roleId);
        if (!$role) {
            return $this->json($response, ['message' => 'Role not found'], 404);
        }

        $data = (array) $request->getParsedBody();
        $permissions = $data['permissions'] ?? [];

        if (!is_array($permissions)) {
            return $this->json($response, ['message' => 'Invalid permissions data'], 422);
        }

        \DB::transaction(function () use ($role, $permissions) {
            // Mevcut tüm izinleri sil
            MenuPermission::where('role_id', $role->getKey())->delete();

            // Yeni izinleri ekle
            foreach ($permissions as $permission) {
                if (isset($permission['menuItemId']) && isset($permission['isVisible'])) {
                    $menuItemId = $permission['menuItemId'];
                    $isVisible = $permission['isVisible'];

                    // Menu item'ın varlığını kontrol et
                    $menuItem = MenuItem::find($menuItemId);
                    if ($menuItem) {
                        MenuPermission::create([
                            'role_id' => $role->getKey(),
                            'menu_item_id' => $menuItemId,
                            'is_visible' => $isVisible,
                        ]);
                    }
                }
            }
        });

        return $this->getPermissions($request, $response, $args);
    }

    public function getMyMenu(Request $request, Response $response): Response
    {
        $user = $request->getAttribute('user');
        
        if (!$user) {
            return $this->json($response, ['message' => 'Unauthorized'], 401);
        }

        // Kullanıcının rollerini al
        $roles = $user->roles()->get();
        
        // Eğer administrator rolü varsa, tüm menüleri göster
        if ($roles->pluck('key')->contains('administrator')) {
            $menuItems = MenuItem::where('is_active', true)
                ->orderBy('sort_order')
                ->get();
        } else {
            // Rolüne göre filtrelenmiş menüleri al
            $roleIds = $roles->pluck('id')->toArray();
            
            $menuItems = MenuItem::where('is_active', true)
                ->whereHas('menuPermissions', function ($query) use ($roleIds) {
                    $query->whereIn('role_id', $roleIds)
                          ->where('is_visible', true);
                })
                ->orderBy('sort_order')
                ->get();
        }

        $result = $this->buildHierarchicalMenu($menuItems);

        return $this->json($response, $result);
    }

    public function store(Request $request, Response $response): Response
    {
        $data = (array) $request->getParsedBody();

        $menuItem = \DB::transaction(function () use ($data) {
            $menuItem = new MenuItem();
            $menuItem->path = $data['path'] ?? '';
            $menuItem->label = $data['label'] ?? '';
            $menuItem->icon = $data['icon'] ?? '';
            $menuItem->sort_order = $data['sortOrder'] ?? 0;
            $menuItem->is_active = $data['isActive'] ?? true;
            $menuItem->parent_id = $data['parentId'] ?? null;
            $menuItem->save();

            return $menuItem;
        });

        return $this->json($response, [
            'id' => $menuItem->getKey(),
            'path' => $menuItem->path,
            'label' => $menuItem->label,
            'icon' => $menuItem->icon,
            'sortOrder' => $menuItem->sort_order,
            'isActive' => $menuItem->is_active,
            'parentId' => $menuItem->parent_id,
            'createdAt' => $menuItem->created_at ? $menuItem->created_at->toDateTimeString() : null,
            'updatedAt' => $menuItem->updated_at ? $menuItem->updated_at->toDateTimeString() : null,
        ], 201);
    }

    public function show(Request $request, Response $response, array $args): Response
    {
        $menuItem = MenuItem::find($args['id'] ?? null);
        if (!$menuItem) {
            return $this->json($response, ['message' => 'Menu item not found'], 404);
        }

        return $this->json($response, [
            'id' => $menuItem->getKey(),
            'path' => $menuItem->path,
            'label' => $menuItem->label,
            'icon' => $menuItem->icon,
            'sortOrder' => $menuItem->sort_order,
            'isActive' => $menuItem->is_active,
            'parentId' => $menuItem->parent_id,
            'createdAt' => $menuItem->created_at ? $menuItem->created_at->toDateTimeString() : null,
            'updatedAt' => $menuItem->updated_at ? $menuItem->updated_at->toDateTimeString() : null,
        ]);
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $menuItem = MenuItem::find($args['id'] ?? null);
        if (!$menuItem) {
            return $this->json($response, ['message' => 'Menu item not found'], 404);
        }

        $data = (array) $request->getParsedBody();

        if (isset($data['path'])) {
            $menuItem->path = $data['path'];
        }
        if (isset($data['label'])) {
            $menuItem->label = $data['label'];
        }
        if (isset($data['icon'])) {
            $menuItem->icon = $data['icon'];
        }
        if (isset($data['sortOrder'])) {
            $menuItem->sort_order = $data['sortOrder'];
        }
        if (isset($data['isActive'])) {
            $menuItem->is_active = $data['isActive'];
        }
        if (isset($data['parentId'])) {
            $menuItem->parent_id = $data['parentId'];
        }

        $menuItem->save();

        return $this->json($response, [
            'id' => $menuItem->getKey(),
            'path' => $menuItem->path,
            'label' => $menuItem->label,
            'icon' => $menuItem->icon,
            'sortOrder' => $menuItem->sort_order,
            'isActive' => $menuItem->is_active,
            'parentId' => $menuItem->parent_id,
            'createdAt' => $menuItem->created_at ? $menuItem->created_at->toDateTimeString() : null,
            'updatedAt' => $menuItem->updated_at ? $menuItem->updated_at->toDateTimeString() : null,
        ]);
    }

    public function destroy(Request $request, Response $response, array $args): Response
    {
        $menuItem = MenuItem::find($args['id'] ?? null);
        if (!$menuItem) {
            return $this->json($response, ['message' => 'Menu item not found'], 404);
        }

        $menuItem->delete();

        return $this->json($response, ['message' => 'Menu item deleted']);
    }

    /**
     * Build hierarchical menu structure from flat menu items
     */
    private function buildHierarchicalMenu(Collection $menuItems, ?Role $role = null): array
    {
        $menuArray = $menuItems->map(fn (MenuItem $item) => [
            'id' => $item->getKey(),
            'path' => $item->path,
            'label' => $item->label,
            'icon' => $item->icon,
            'sortOrder' => $item->sort_order,
            'isActive' => $item->is_active,
            'parentId' => $item->parent_id,
            'createdAt' => $item->created_at ? $item->created_at->toDateTimeString() : null,
            'updatedAt' => $item->updated_at ? $item->updated_at->toDateTimeString() : null,
            // If role is provided, check visibility
            'isVisible' => $role ? (
                ($permission = $item->menuPermissions()
                    ->where('role_id', $role->getKey())
                    ->first()) ? $permission->is_visible : false
            ) : null,
        ])->values()->all();

        // Build hierarchical structure
        $indexed = [];
        $rootItems = [];

        // First pass: index all items
        foreach ($menuArray as $item) {
            $item['children'] = [];
            $indexed[$item['id']] = $item;
        }

        // Second pass: build hierarchy
        foreach ($indexed as $id => $item) {
            if ($item['parentId'] && isset($indexed[$item['parentId']])) {
                $indexed[$item['parentId']]['children'][] = &$indexed[$id];
            } else {
                $rootItems[] = &$indexed[$id];
            }
        }

        // Sort children by sort_order
        $this->sortMenuItemsRecursive($rootItems);

        return $rootItems;
    }

    /**
     * Sort menu items and their children recursively
     */
    private function sortMenuItemsRecursive(array &$items): void
    {
        usort($items, fn ($a, $b) => $a['sortOrder'] - $b['sortOrder']);

        foreach ($items as &$item) {
            if (!empty($item['children'])) {
                $this->sortMenuItemsRecursive($item['children']);
            }
        }
    }
}
