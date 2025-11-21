import React, { useEffect, useMemo, useState } from 'react';
import { MenuService, MenuItem, RoleMenuPermission } from '../services/MenuService';
import { useAuth } from '../context/AuthContext';

interface Role {
  id: string;
  name: string;
  key: string;
}

type MenuTreeItem = MenuItem & { children?: MenuTreeItem[] };

export const MenuManagementPage: React.FC = () => {
  const { user } = useAuth();
  const [roles, setRoles] = useState<Role[]>([]);
  const [selectedRole, setSelectedRole] = useState<string>('');
  const [menuItems, setMenuItems] = useState<MenuTreeItem[]>([]);
  const [visibilityMap, setVisibilityMap] = useState<Record<string, boolean>>({});
  const [loadingPermissions, setLoadingPermissions] = useState(false);
  const [savingPermissions, setSavingPermissions] = useState(false);
  const [savingOrder, setSavingOrder] = useState(false);
  const [structureDirty, setStructureDirty] = useState(false);
  const [expandedIds, setExpandedIds] = useState<Set<string>>(new Set());

  useEffect(() => {
    loadRoles();
    loadMenuItems();
  }, []);

  useEffect(() => {
    if (selectedRole) {
      loadMenuPermissions(selectedRole);
    } else {
      setVisibilityMap({});
    }
  }, [selectedRole]);

  const loadRoles = async () => {
    try {
      const response = await fetch('/api/roles');
      const data = await response.json();
      setRoles(data);
    } catch (error) {
      console.error('Roller yüklenirken hata:', error);
    }
  };

  const loadMenuItems = async () => {
    try {
      const items = await MenuService.getAllMenuItems();
      setMenuItems(items);
      setExpandedIds(collectExpandableIds(items));
    } catch (error) {
      console.error('Menü öğeleri yüklenirken hata:', error);
    }
  };

  const loadMenuPermissions = async (roleId: string) => {
    setLoadingPermissions(true);
    try {
      const permissions = await MenuService.getRoleMenuPermissions(roleId);
      const map: Record<string, boolean> = {};
      collectPermissionMap(permissions as MenuTreeItem[], map);
      setVisibilityMap(map);
    } catch (error) {
      console.error('Menü izinleri yüklenirken hata:', error);
    } finally {
      setLoadingPermissions(false);
    }
  };

  const collectPermissionMap = (items: MenuTreeItem[], map: Record<string, boolean>) => {
    items.forEach((item) => {
      if (typeof item.isVisible === 'boolean') {
        map[item.id] = item.isVisible;
      }
      if (item.children?.length) {
        collectPermissionMap(item.children, map);
      }
    });
  };

  const collectExpandableIds = (items: MenuTreeItem[], ids: Set<string> = new Set()) => {
    items.forEach((item) => {
      if (item.children?.length) {
        ids.add(item.id);
        collectExpandableIds(item.children, ids);
      }
    });
    return ids;
  };

  const flattenWithDepth = (items: MenuTreeItem[], depth = 0, parentId: string | null = null) => {
    const result: Array<MenuTreeItem & { depth: number; parentId: string | null }> = [];
    items.forEach((item) => {
      result.push({ ...item, depth, parentId });
      if (item.children?.length) {
        result.push(...flattenWithDepth(item.children, depth + 1, item.id));
      }
    });
    return result;
  };

  const togglePermission = (menuItemId: string, isVisible: boolean) => {
    setVisibilityMap((prev) => ({ ...prev, [menuItemId]: isVisible }));
  };

  const handleSavePermissions = async () => {
    if (!selectedRole) return;

    setSavingPermissions(true);
    try {
      const allItems = flattenWithDepth(menuItems);
      const rolePermissions: RoleMenuPermission[] = allItems.map((item) => ({
        menuItemId: item.id,
        isVisible: visibilityMap[item.id] ?? false,
      }));

      await MenuService.updateRoleMenuPermissions(selectedRole, rolePermissions);
      alert('Menü izinleri başarıyla güncellendi!');
    } catch (error) {
      console.error('Menü izinleri güncellenirken hata:', error);
      alert('Menü izinleri güncellenirken bir hata oluştu!');
    } finally {
      setSavingPermissions(false);
    }
  };

  const handleAddMenuItem = async () => {
    const label = prompt('Yeni menü adı:');
    const path = prompt('Menü yolu (örn: /yeni-menu):');
    const icon = prompt('İkon adı (örn: home):');

    if (!label || !path || !icon) return;

    try {
      await MenuService.createMenuItem({
        label,
        path,
        icon,
        sortOrder: menuItems.length + 1,
        isActive: true,
      });
      await loadMenuItems();
      alert('Menü öğesi başarıyla eklendi!');
    } catch (error) {
      console.error('Menü öğesi eklenirken hata:', error);
      alert('Menü öğesi eklenirken bir hata oluştu!');
    }
  };

  const handleAddFolder = async () => {
    const label = prompt('Klasör adı:');
    if (!label) return;

    try {
      await MenuService.createMenuItem({
        label,
        path: '#',
        icon: 'folder',
        sortOrder: menuItems.length + 1,
        isActive: true,
      });
      await loadMenuItems();
      alert('Klasör oluşturuldu. İçerisine menüleri taşıyabilirsiniz.');
    } catch (error) {
      console.error('Klasör eklenirken hata:', error);
      alert('Klasör eklenirken bir hata oluştu!');
    }
  };

  const handleDeleteMenuItem = async (menuItemId: string) => {
    if (!confirm('Bu menü öğesini silmek istediğinizden emin misiniz?')) return;

    try {
      await MenuService.deleteMenuItem(menuItemId);
      await loadMenuItems();
      if (selectedRole) {
        await loadMenuPermissions(selectedRole);
      }
      alert('Menü öğesi başarıyla silindi!');
    } catch (error) {
      console.error('Menü öğesi silinirken hata:', error);
      alert('Menü öğesi silinirken bir hata oluştu!');
    }
  };

  const cloneTree = (items: MenuTreeItem[]): MenuTreeItem[] =>
    items.map((item) => ({
      ...item,
      children: item.children ? cloneTree(item.children) : [],
    }));

  const findLocation = (
    items: MenuTreeItem[],
    id: string,
    parentId: string | null = null
  ): { list: MenuTreeItem[]; index: number; parentId: string | null } | null => {
    for (let i = 0; i < items.length; i++) {
      const item = items[i];
      if (item.id === id) {
        return { list: items, index: i, parentId };
      }
      if (item.children?.length) {
        const found = findLocation(item.children, id, item.id);
        if (found) return found;
      }
    }
    return null;
  };

  const normalizeSortOrders = (items: MenuTreeItem[]): MenuTreeItem[] => {
    return items.map((item, idx) => ({
      ...item,
      sortOrder: idx + 1,
      children: item.children ? normalizeSortOrders(item.children) : [],
    }));
  };

  const collectDescendants = (item?: MenuTreeItem): Set<string> => {
    const ids = new Set<string>();
    if (!item) return ids;
    const walk = (node: MenuTreeItem) => {
      node.children?.forEach((child) => {
        ids.add(child.id);
        walk(child);
      });
    };
    walk(item);
    return ids;
  };

  const moveItemOrder = (itemId: string, direction: 'up' | 'down') => {
    setMenuItems((prev) => {
      const tree = cloneTree(prev);
      const location = findLocation(tree, itemId);
      if (!location) return prev;

      const { list, index } = location;
      const newIndex = direction === 'up' ? index - 1 : index + 1;
      if (newIndex < 0 || newIndex >= list.length) return prev;

      [list[index], list[newIndex]] = [list[newIndex], list[index]];
      setStructureDirty(true);
      return normalizeSortOrders(tree);
    });
  };

  const removeItemFromTree = (
    items: MenuTreeItem[],
    id: string
  ): { tree: MenuTreeItem[]; removed?: MenuTreeItem } => {
    for (let i = 0; i < items.length; i++) {
      const item = items[i];
      if (item.id === id) {
        const [removed] = items.splice(i, 1);
        return { tree: items, removed };
      }
      if (item.children?.length) {
        const result = removeItemFromTree(item.children, id);
        if (result.removed) {
          item.children = result.tree;
          return { tree: items, removed: result.removed };
        }
      }
    }
    return { tree: items };
  };

  const findItemById = (items: MenuTreeItem[], id: string): MenuTreeItem | undefined => {
    for (const item of items) {
      if (item.id === id) return item;
      if (item.children?.length) {
        const found = findItemById(item.children, id);
        if (found) return found;
      }
    }
    return undefined;
  };

  const moveItemToParent = (itemId: string, targetParentId: string | null) => {
    setMenuItems((prev) => {
      const tree = cloneTree(prev);
      const currentItem = findItemById(tree, itemId);
      if (!currentItem) return prev;

      if (targetParentId === itemId) return prev;
      const descendantIds = collectDescendants(currentItem);
      if (targetParentId && descendantIds.has(targetParentId)) {
        alert('Bir menüyü kendi altına taşıyamazsınız.');
        return prev;
      }

      const { tree: withoutItem, removed } = removeItemFromTree(tree, itemId);
      if (!removed) return prev;

      const insertInto = (items: MenuTreeItem[], parentId: string | null): MenuTreeItem[] => {
        if (!parentId) {
          items.push({ ...removed, parentId: null });
          return items;
        }
        return items.map((item) => {
          if (item.id === parentId) {
            const children = item.children ? [...item.children, { ...removed, parentId }] : [{ ...removed, parentId }];
            return { ...item, children };
          }
          if (item.children?.length) {
            return { ...item, children: insertInto(item.children, parentId) };
          }
          return item;
        });
      };

      const newTree = insertInto(withoutItem, targetParentId);
      setStructureDirty(true);
      return normalizeSortOrders(newTree);
    });
  };

  const handleSaveStructure = async () => {
    setSavingOrder(true);
    try {
      const updates: { id: string; sortOrder: number; parentId: string | null }[] = [];
      const walk = (items: MenuTreeItem[], parentId: string | null = null) => {
        items.forEach((item, index) => {
          updates.push({ id: item.id, sortOrder: index + 1, parentId });
          if (item.children?.length) {
            walk(item.children, item.id);
          }
        });
      };
      walk(menuItems);

      await Promise.all(
        updates.map((update) =>
          MenuService.updateMenuItem(update.id, {
            sortOrder: update.sortOrder,
            parentId: update.parentId,
          })
        )
      );
      setStructureDirty(false);
      await loadMenuItems();
      alert('Menü sırası ve klasör değişiklikleri kaydedildi.');
    } catch (error) {
      console.error('Menü yapısı kaydedilirken hata:', error);
      alert('Menü yapısı kaydedilirken bir hata oluştu!');
    } finally {
      setSavingOrder(false);
    }
  };

  const isAdmin = user?.roles?.some((role: any) => {
    if (typeof role === 'string') {
      return role === 'administrator';
    }
    return role?.key === 'administrator';
  });

  const debugInfo = {
    hasUser: !!user,
    userId: user?.id,
    userEmail: user?.email,
    hasRoles: !!user?.roles,
    rolesCount: user?.roles?.length || 0,
    roles: user?.roles,
    isAdmin,
  };

  const folderOptions = useMemo(() => {
    const options = [{ id: '', label: 'Kök (klasör yok)', depth: 0 }];
    flattenWithDepth(menuItems).forEach((item) => {
      const isFolder = item.path === '#' || (item.children?.length ?? 0) > 0;
      if (isFolder) {
        options.push({
          id: item.id,
          label: `${'‣ '.repeat(item.depth)}${item.label}`,
          depth: item.depth,
        });
      }
    });
    return options;
  }, [menuItems]);

  const toggleExpand = (id: string) => {
    setExpandedIds((prev) => {
      const next = new Set(prev);
      if (next.has(id)) {
        next.delete(id);
      } else {
        next.add(id);
      }
      return next;
    });
  };

  const renderMenuRow = (item: MenuTreeItem, depth = 0) => {
    const hasChildren = item.children?.length;
    const isExpanded = expandedIds.has(item.id);
    const isFolder = item.path === '#' || !!hasChildren;

    return (
      <div key={item.id} className="border border-gray-200 bg-white rounded-lg p-3">
        <div className="flex items-center gap-3">
          <div className="flex items-center gap-2" style={{ marginLeft: depth * 12 }}>
            {hasChildren && (
              <button
                type="button"
                onClick={() => toggleExpand(item.id)}
                className="p-1 text-gray-500 hover:text-gray-700"
                title={isExpanded ? 'Daralt' : 'Genişlet'}
              >
                <span className="material-symbols-outlined text-sm">
                  {isExpanded ? 'expand_less' : 'expand_more'}
                </span>
              </button>
            )}
            <span className="material-symbols-outlined text-gray-600">{item.icon}</span>
            <div>
              <div className="font-semibold text-gray-900 flex items-center gap-2">
                {item.label}
                {isFolder && (
                  <span className="text-xs bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full">
                    Klasör
                  </span>
                )}
              </div>
              <div className="text-xs text-gray-500">{item.path || '—'}</div>
            </div>
          </div>

          <div className="ml-auto flex items-center gap-2">
            <div className="flex items-center gap-1">
              <button
                type="button"
                onClick={() => moveItemOrder(item.id, 'up')}
                className="p-1 rounded border border-gray-200 text-gray-600 hover:bg-gray-50"
                title="Yukarı taşı"
              >
                <span className="material-symbols-outlined text-sm">arrow_upward</span>
              </button>
              <button
                type="button"
                onClick={() => moveItemOrder(item.id, 'down')}
                className="p-1 rounded border border-gray-200 text-gray-600 hover:bg-gray-50"
                title="Aşağı taşı"
              >
                <span className="material-symbols-outlined text-sm">arrow_downward</span>
              </button>
            </div>
            <select
              value={item.parentId || ''}
              onChange={(e) => moveItemToParent(item.id, e.target.value || null)}
              className="text-sm border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
              title="Klasöre taşı"
            >
              {folderOptions
                .filter((option) => option.id === '' || option.id !== item.id)
                .map((option) => (
                  <option key={option.id} value={option.id}>
                    {option.label}
                  </option>
                ))}
            </select>
            {selectedRole && (
              <label className="flex items-center gap-2 px-2 py-1 rounded border border-gray-200 bg-gray-50 cursor-pointer">
                <input
                  type="checkbox"
                  checked={visibilityMap[item.id] ?? false}
                  onChange={(e) => togglePermission(item.id, e.target.checked)}
                  className="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                />
                <span className="text-sm text-gray-700">Görünür</span>
              </label>
            )}
            <button
              onClick={() => handleDeleteMenuItem(item.id)}
              className="p-1 text-red-600 hover:bg-red-50 rounded transition-colors"
              title="Sil"
            >
              <span className="material-symbols-outlined text-sm">delete</span>
            </button>
          </div>
        </div>
        {hasChildren && isExpanded && (
          <div className="mt-2 space-y-2">
            {item.children!.map((child) => renderMenuRow(child, depth + 1))}
          </div>
        )}
      </div>
    );
  };

  if (!isAdmin) {
    return (
      <div className="p-8">
        <div className="bg-red-50 border border-red-200 rounded-lg p-4">
          <h2 className="text-red-800 font-semibold mb-2">Erişim Engellendi</h2>
          <p className="text-red-600">Bu sayfaya sadece administrator rolüne sahip kullanıcılar erişebilir.</p>
          <details className="mt-4">
            <summary className="text-red-600 text-sm cursor-pointer">Debug Bilgisi</summary>
            <pre className="text-xs text-red-500 mt-2 bg-red-100 p-2 rounded overflow-auto">
              {JSON.stringify(debugInfo, null, 2)}
            </pre>
          </details>
        </div>
      </div>
    );
  }

  return (
    <div className="p-8">
      <div className="max-w-6xl mx-auto">
        <div className="bg-white rounded-lg shadow-sm border border-gray-200">
          <div className="p-6 border-b border-gray-200 flex flex-col gap-2">
            <div className="flex items-center justify-between gap-4">
              <div>
                <h1 className="text-2xl font-bold text-gray-900">Menü Yönetimi</h1>
                <p className="text-gray-600 mt-1">
                  Menüler için sıralama, klasörleme ve rol bazlı görünürlük ayarlarını yönetin.
                </p>
              </div>
              <div className="flex gap-2">
                <button
                  onClick={handleAddFolder}
                  className="px-3 py-2 bg-amber-100 text-amber-800 rounded-lg text-sm hover:bg-amber-200 transition-colors"
                >
                  Klasör Ekle
                </button>
                <button
                  onClick={handleAddMenuItem}
                  className="px-3 py-2 bg-green-600 text-white rounded-lg text-sm hover:bg-green-700 transition-colors"
                >
                  Menü Ekle
                </button>
              </div>
            </div>
            <div className="flex flex-wrap gap-2">
              <span className="text-xs text-gray-600 bg-gray-100 px-2 py-1 rounded">
                Sırayı ok tuşlarıyla değiştirin, açılır listesinden klasör seçerek içeri/çıkara taşıyın.
              </span>
              <span className="text-xs text-gray-600 bg-gray-100 px-2 py-1 rounded">
                Değişiklikleri kaydetmek için "Yapıyı Kaydet" butonunu kullanın.
              </span>
            </div>
          </div>

          <div className="p-6">
            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
              <div className="lg:col-span-1">
                <div className="bg-gray-50 rounded-lg p-4">
                  <h3 className="font-semibold text-gray-900 mb-4">Rol Seçimi</h3>
                  <div className="space-y-2">
                    {roles.map((role) => (
                      <button
                        key={role.id}
                        onClick={() => setSelectedRole(role.id)}
                        className={`w-full text-left px-3 py-2 rounded-lg transition-colors ${
                          selectedRole === role.id
                            ? 'bg-blue-100 text-blue-700 border border-blue-200'
                            : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50'
                        }`}
                      >
                        {role.name}
                      </button>
                    ))}
                  </div>
                </div>
              </div>

              <div className="lg:col-span-2">
                <div className="bg-gray-50 rounded-lg p-4">
                  <div className="flex flex-wrap justify-between items-center gap-3 mb-4">
                    <div>
                      <h3 className="font-semibold text-gray-900">
                        {selectedRole ? 'Menü İzinleri ve Sıralama' : 'Önce Rol Seçin'}
                      </h3>
                      <p className="text-sm text-gray-600">
                        Menüyü sürüklemek yerine oklarla kaydırın, klasöre alın veya çıkarın.
                      </p>
                    </div>
                    <div className="flex gap-2">
                      <button
                        onClick={handleSaveStructure}
                        disabled={savingOrder || !structureDirty}
                        className="px-3 py-1.5 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700 transition-colors disabled:opacity-50"
                      >
                        {savingOrder ? 'Kaydediliyor...' : structureDirty ? 'Yapıyı Kaydet' : 'Yapı Güncel'}
                      </button>
                      {selectedRole && (
                        <button
                          onClick={handleSavePermissions}
                          disabled={savingPermissions}
                          className="px-3 py-1.5 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700 transition-colors disabled:opacity-50"
                        >
                          {savingPermissions ? 'İzin Kaydediliyor...' : 'İzinleri Kaydet'}
                        </button>
                      )}
                    </div>
                  </div>

                  {loadingPermissions && selectedRole ? (
                    <div className="text-center py-8">
                      <div className="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                      <p className="text-gray-600 mt-2">Yükleniyor...</p>
                    </div>
                  ) : selectedRole ? (
                    <div className="space-y-2">
                      {menuItems.map((item) => renderMenuRow(item))}
                    </div>
                  ) : (
                    <div className="text-center py-8">
                      <p className="text-gray-500">Menü düzenlemek ve izinleri görmek için lütfen bir rol seçin.</p>
                    </div>
                  )}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};
