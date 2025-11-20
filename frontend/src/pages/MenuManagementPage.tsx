import React, { useState, useEffect } from 'react';
import { MenuService, MenuItem, MenuPermission, RoleMenuPermission } from '../services/MenuService';
import { useAuth } from '../context/AuthContext';

interface Role {
  id: string;
  name: string;
  key: string;
}

export const MenuManagementPage: React.FC = () => {
  const { user } = useAuth();
  const [roles, setRoles] = useState<Role[]>([]);
  const [selectedRole, setSelectedRole] = useState<string>('');
  const [menuItems, setMenuItems] = useState<MenuItem[]>([]);
  const [menuPermissions, setMenuPermissions] = useState<MenuPermission[]>([]);
  const [loading, setLoading] = useState(false);
  const [saving, setSaving] = useState(false);

  useEffect(() => {
    loadRoles();
    loadMenuItems();
  }, []);

  useEffect(() => {
    if (selectedRole) {
      loadMenuPermissions(selectedRole);
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
    } catch (error) {
      console.error('Menü öğeleri yüklenirken hata:', error);
    }
  };

  const loadMenuPermissions = async (roleId: string) => {
    setLoading(true);
    try {
      const permissions = await MenuService.getRoleMenuPermissions(roleId);
      setMenuPermissions(permissions);
    } catch (error) {
      console.error('Menü izinleri yüklenirken hata:', error);
    } finally {
      setLoading(false);
    }
  };

  const handlePermissionChange = (menuItemId: string, isVisible: boolean) => {
    setMenuPermissions(prev => 
      prev.map(permission => 
        permission.id === menuItemId 
          ? { ...permission, isVisible }
          : permission
      )
    );
  };

  const handleSavePermissions = async () => {
    if (!selectedRole) return;

    setSaving(true);
    try {
      const rolePermissions: RoleMenuPermission[] = menuPermissions.map(permission => ({
        menuItemId: permission.id,
        isVisible: permission.isVisible
      }));

      await MenuService.updateRoleMenuPermissions(selectedRole, rolePermissions);
      alert('Menü izinleri başarıyla güncellendi!');
    } catch (error) {
      console.error('Menü izinleri güncellenirken hata:', error);
      alert('Menü izinleri güncellenirken bir hata oluştu!');
    } finally {
      setSaving(false);
    }
  };

  const handleAddMenuItem = async () => {
    const label = prompt('Yeni menü öğesi adı:');
    const path = prompt('Menü yolu (örn: /new-menu):');
    const icon = prompt('İkon adı:');

    if (!label || !path || !icon) return;

    try {
      await MenuService.createMenuItem({
        label,
        path,
        icon,
        sortOrder: menuItems.length + 1,
        isActive: true
      });
      await loadMenuItems();
      alert('Menü öğesi başarıyla eklendi!');
    } catch (error) {
      console.error('Menü öğesi eklenirken hata:', error);
      alert('Menü öğesi eklenirken bir hata oluştu!');
    }
  };

  const handleDeleteMenuItem = async (menuItemId: string) => {
    if (!confirm('Bu menü öğesini silmek istediğinizden emin misiniz?')) return;

    try {
      await MenuService.deleteMenuItem(menuItemId);
      await loadMenuItems();
      await loadMenuPermissions(selectedRole);
      alert('Menü öğesi başarıyla silindi!');
    } catch (error) {
      console.error('Menü öğesi silinirken hata:', error);
      alert('Menü öğesi silinirken bir hata oluştu!');
    }
  };

  // Sadece admin kullanıcılarının erişebilmesi için kontrol
  const isAdmin = user?.roles?.some((role: any) => {
    if (typeof role === 'string') {
      return role === 'administrator';
    }
    return role?.key === 'administrator';
  });
  
  // Add debugging information
  const debugInfo = {
    hasUser: !!user,
    userId: user?.id,
    userEmail: user?.email,
    hasRoles: !!user?.roles,
    rolesCount: user?.roles?.length || 0,
    roles: user?.roles,
    isAdmin
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
          <div className="p-6 border-b border-gray-200">
            <h1 className="text-2xl font-bold text-gray-900">Menü Yönetimi</h1>
            <p className="text-gray-600 mt-2">Roller için menü erişim izinlerini yönetin.</p>
          </div>

          <div className="p-6">
            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
              {/* Rol Seçimi */}
              <div className="lg:col-span-1">
                <div className="bg-gray-50 rounded-lg p-4">
                  <h3 className="font-semibold text-gray-900 mb-4">Rol Seçimi</h3>
                  <div className="space-y-2">
                    {roles.map(role => (
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

              {/* Menü İzinleri */}
              <div className="lg:col-span-2">
                <div className="bg-gray-50 rounded-lg p-4">
                  <div className="flex justify-between items-center mb-4">
                    <h3 className="font-semibold text-gray-900">
                      {selectedRole ? 'Menü İzinleri' : 'Önce Rol Seçin'}
                    </h3>
                    <div className="space-x-2">
                      <button
                        onClick={handleAddMenuItem}
                        className="px-3 py-1 bg-green-600 text-white rounded-lg text-sm hover:bg-green-700 transition-colors"
                      >
                        Menü Ekle
                      </button>
                      {selectedRole && (
                        <button
                          onClick={handleSavePermissions}
                          disabled={saving}
                          className="px-3 py-1 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700 transition-colors disabled:opacity-50"
                        >
                          {saving ? 'Kaydediliyor...' : 'Kaydet'}
                        </button>
                      )}
                    </div>
                  </div>

                  {loading ? (
                    <div className="text-center py-8">
                      <div className="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                      <p className="text-gray-600 mt-2">Yükleniyor...</p>
                    </div>
                  ) : selectedRole ? (
                    <div className="space-y-2">
                      {menuItems.map(item => (
                        <div
                          key={item.id}
                          className="flex items-center justify-between p-3 bg-white rounded-lg border border-gray-200"
                        >
                          <div className="flex items-center space-x-3">
                            <span className="material-symbols-outlined text-gray-600">
                              {item.icon}
                            </span>
                            <div>
                              <div className="font-medium text-gray-900">{item.label}</div>
                              <div className="text-sm text-gray-500">{item.path}</div>
                            </div>
                          </div>
                          <div className="flex items-center space-x-3">
                            <label className="flex items-center cursor-pointer">
                              <input
                                type="checkbox"
                                checked={menuPermissions.find(p => p.id === item.id)?.isVisible || false}
                                onChange={(e) => handlePermissionChange(item.id, e.target.checked)}
                                className="mr-2 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                              />
                              <span className="text-sm text-gray-700">
                                {menuPermissions.find(p => p.id === item.id)?.isVisible ? 'Görünür' : 'Gizli'}
                              </span>
                            </label>
                            <button
                              onClick={() => handleDeleteMenuItem(item.id)}
                              className="p-1 text-red-600 hover:bg-red-50 rounded transition-colors"
                              title="Sil"
                            >
                              <span className="material-symbols-outlined text-sm">delete</span>
                            </button>
                          </div>
                        </div>
                      ))}
                    </div>
                  ) : (
                    <div className="text-center py-8">
                      <p className="text-gray-500">Menü izinlerini görüntülemek için lütfen bir rol seçin.</p>
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
