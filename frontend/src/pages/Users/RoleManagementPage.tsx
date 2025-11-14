import { useState } from 'react';
import { RolesApi, Role, Permission, UpdateRolePermissionsRequest } from '../../api/modules/users';
import { useAsyncData } from '../../hooks/useAsyncData';

export const RoleManagementPage = () => {
  const [selectedRole, setSelectedRole] = useState('avukat');
  const [hasChanges, setHasChanges] = useState(false);
  const { data: rolesData, isLoading, refetch } = useAsyncData(['roles'], RolesApi.getRoles);

  // Default roles with their permissions (fallback when API is not available)
  const defaultRoles: Role[] = [
    {
      id: 'admin',
      name: 'Admin',
      permissions: [
        { id: '1', name: 'Tüm Davaları Görüntüle', code: 'CASE_VIEW_ALL', description: 'Sistemdeki tüm davalara erişim', enabled: true },
        { id: '2', name: 'Atanmış Davaları Görüntüle', code: 'CASE_VIEW_ASSIGNED', description: 'Sadece atanmış davaları görüntüle', enabled: true },
        { id: '3', name: 'Dava Düzenle', code: 'CASE_EDIT', description: 'Dava bilgilerini düzenleme yetkisi', enabled: true },
        { id: '4', name: 'Yeni Dava Oluştur', code: 'CASE_CREATE', description: 'Yeni dava oluşturma yetkisi', enabled: true },
        { id: '5', name: 'Dava Sil', code: 'CASE_DELETE', description: 'Dava silme yetkisi', enabled: true },
        { id: '6', name: 'Doküman Yükle', code: 'DOC_UPLOAD', description: 'Doküman yükleme yetkisi', enabled: true },
        { id: '7', name: 'Doküman Görüntüle', code: 'DOC_VIEW', description: 'Doküman görüntüleme yetkisi', enabled: true },
        { id: '8', name: 'Müvekkil Sil', code: 'CLIENT_DELETE', description: 'Müvekkil silme yetkisi', enabled: true },
        { id: '9', name: 'Kullanıcı Yönetimi', code: 'USER_MANAGE', description: 'Kullanıcı ekleme/düzenleme/silme', enabled: true },
        { id: '10', name: 'Rol Yönetimi', code: 'ROLE_MANAGE', description: 'Rol ve yetki yönetimi', enabled: true },
      ]
    },
    {
      id: 'avukat',
      name: 'Avukat',
      permissions: [
        { id: '1', name: 'Tüm Davaları Görüntüle', code: 'CASE_VIEW_ALL', description: 'Sistemdeki tüm davalara erişim', enabled: true },
        { id: '2', name: 'Atanmış Davaları Görüntüle', code: 'CASE_VIEW_ASSIGNED', description: 'Sadece atanmış davaları görüntüle', enabled: true },
        { id: '3', name: 'Dava Düzenle', code: 'CASE_EDIT', description: 'Dava bilgilerini düzenleme yetkisi', enabled: true },
        { id: '4', name: 'Yeni Dava Oluştur', code: 'CASE_CREATE', description: 'Yeni dava oluşturma yetkisi', enabled: true },
        { id: '5', name: 'Dava Sil', code: 'CASE_DELETE', description: 'Dava silme yetkisi', enabled: false },
        { id: '6', name: 'Doküman Yükle', code: 'DOC_UPLOAD', description: 'Doküman yükleme yetkisi', enabled: true },
        { id: '7', name: 'Doküman Görüntüle', code: 'DOC_VIEW', description: 'Doküman görüntüleme yetkisi', enabled: true },
        { id: '8', name: 'Müvekkil Sil', code: 'CLIENT_DELETE', description: 'Müvekkil silme yetkisi', enabled: false },
        { id: '9', name: 'Kullanıcı Yönetimi', code: 'USER_MANAGE', description: 'Kullanıcı ekleme/düzenleme/silme', enabled: false },
        { id: '10', name: 'Rol Yönetimi', code: 'ROLE_MANAGE', description: 'Rol ve yetki yönetimi', enabled: false },
      ]
    },
    {
      id: 'stajyer',
      name: 'Stajyer',
      permissions: [
        { id: '1', name: 'Tüm Davaları Görüntüle', code: 'CASE_VIEW_ALL', description: 'Sistemdeki tüm davalara erişim', enabled: false },
        { id: '2', name: 'Atanmış Davaları Görüntüle', code: 'CASE_VIEW_ASSIGNED', description: 'Sadece atanmış davaları görüntüle', enabled: true },
        { id: '3', name: 'Dava Düzenle', code: 'CASE_EDIT', description: 'Dava bilgilerini düzenleme yetkisi', enabled: false },
        { id: '4', name: 'Yeni Dava Oluştur', code: 'CASE_CREATE', description: 'Yeni dava oluşturma yetkisi', enabled: false },
        { id: '5', name: 'Dava Sil', code: 'CASE_DELETE', description: 'Dava silme yetkisi', enabled: false },
        { id: '6', name: 'Doküman Yükle', code: 'DOC_UPLOAD', description: 'Doküman yükleme yetkisi', enabled: true },
        { id: '7', name: 'Doküman Görüntüle', code: 'DOC_VIEW', description: 'Doküman görüntüleme yetkisi', enabled: true },
        { id: '8', name: 'Müvekkil Sil', code: 'CLIENT_DELETE', description: 'Müvekkil silme yetkisi', enabled: false },
        { id: '9', name: 'Kullanıcı Yönetimi', code: 'USER_MANAGE', description: 'Kullanıcı ekleme/düzenleme/silme', enabled: false },
        { id: '10', name: 'Rol Yönetimi', code: 'ROLE_MANAGE', description: 'Rol ve yetki yönetimi', enabled: false },
      ]
    },
    {
      id: 'asistan',
      name: 'Asistan',
      permissions: [
        { id: '1', name: 'Tüm Davaları Görüntüle', code: 'CASE_VIEW_ALL', description: 'Sistemdeki tüm davalara erişim', enabled: true },
        { id: '2', name: 'Atanmış Davaları Görüntüle', code: 'CASE_VIEW_ASSIGNED', description: 'Sadece atanmış davaları görüntüle', enabled: true },
        { id: '3', name: 'Dava Düzenle', code: 'CASE_EDIT', description: 'Dava bilgilerini düzenleme yetkisi', enabled: false },
        { id: '4', name: 'Yeni Dava Oluştur', code: 'CASE_CREATE', description: 'Yeni dava oluşturma yetkisi', enabled: false },
        { id: '5', name: 'Dava Sil', code: 'CASE_DELETE', description: 'Dava silme yetkisi', enabled: false },
        { id: '6', name: 'Doküman Yükle', code: 'DOC_UPLOAD', description: 'Doküman yükleme yetkisi', enabled: true },
        { id: '7', name: 'Doküman Görüntüle', code: 'DOC_VIEW', description: 'Doküman görüntüleme yetkisi', enabled: true },
        { id: '8', name: 'Müvekkil Sil', code: 'CLIENT_DELETE', description: 'Müvekkil silme yetkisi', enabled: false },
        { id: '9', name: 'Kullanıcı Yönetimi', code: 'USER_MANAGE', description: 'Kullanıcı ekleme/düzenleme/silme', enabled: false },
        { id: '10', name: 'Rol Yönetimi', code: 'ROLE_MANAGE', description: 'Rol ve yetki yönetimi', enabled: false },
      ]
    },
    {
      id: 'muhasebe',
      name: 'Muhasebe',
      permissions: [
        { id: '1', name: 'Tüm Davaları Görüntüle', code: 'CASE_VIEW_ALL', description: 'Sistemdeki tüm davalara erişim', enabled: true },
        { id: '2', name: 'Atanmış Davaları Görüntüle', code: 'CASE_VIEW_ASSIGNED', description: 'Sadece atanmış davaları görüntüle', enabled: true },
        { id: '3', name: 'Dava Düzenle', code: 'CASE_EDIT', description: 'Dava bilgilerini düzenleme yetkisi', enabled: false },
        { id: '4', name: 'Yeni Dava Oluştur', code: 'CASE_CREATE', description: 'Yeni dava oluşturma yetkisi', enabled: false },
        { id: '5', name: 'Dava Sil', code: 'CASE_DELETE', description: 'Dava silme yetkisi', enabled: false },
        { id: '6', name: 'Doküman Yükle', code: 'DOC_UPLOAD', description: 'Doküman yükleme yetkisi', enabled: true },
        { id: '7', name: 'Doküman Görüntüle', code: 'DOC_VIEW', description: 'Doküman görüntüleme yetkisi', enabled: true },
        { id: '8', name: 'Müvekkil Sil', code: 'CLIENT_DELETE', description: 'Müvekkil silme yetkisi', enabled: false },
        { id: '9', name: 'Kullanıcı Yönetimi', code: 'USER_MANAGE', description: 'Kullanıcı ekleme/düzenleme/silme', enabled: false },
        { id: '10', name: 'Rol Yönetimi', code: 'ROLE_MANAGE', description: 'Rol ve yetki yönetimi', enabled: false },
      ]
    }
  ];

  const roles = rolesData || defaultRoles;

  const [currentRole, setCurrentRole] = useState<Role>(
    roles.find((role: Role) => role.id === selectedRole) || roles[1]
  );

  if (isLoading) return <p>Roller yükleniyor...</p>;

  const handleRoleChange = (roleId: string) => {
    setSelectedRole(roleId);
    const role = roles.find((r: Role) => r.id === roleId);
    if (role) {
      setCurrentRole({...role, permissions: [...role.permissions]});
    }
    setHasChanges(false);
  };

  const handlePermissionToggle = (permissionId: string) => {
    setCurrentRole(prev => ({
      ...prev,
      permissions: prev.permissions.map(permission =>
        permission.id === permissionId
          ? { ...permission, enabled: !permission.enabled }
          : permission
      )
    }));
    setHasChanges(true);
  };

  const handleSave = async () => {
    try {
      const permissionsUpdate = {
        roleId: currentRole.id,
        permissions: currentRole.permissions.map(permission => ({
          id: permission.id,
          enabled: permission.enabled
        }))
      };
      
      await RolesApi.updateRolePermissions(currentRole.id, permissionsUpdate);
      await refetch(); // Refresh the roles data
      setHasChanges(false);
      console.log('Role permissions saved successfully:', currentRole);
    } catch (error) {
      console.error('Error saving role permissions:', error);
      // Handle error (show notification, etc.)
    }
  };

  const handleCancel = () => {
    const originalRole = roles.find((role: Role) => role.id === selectedRole);
    if (originalRole) {
      setCurrentRole({...originalRole, permissions: [...originalRole.permissions]});
    }
    setHasChanges(false);
  };

  return (
    <div className="flex min-h-screen">
      {/* Sidebar */}
      <aside className="w-64 flex-shrink-0 bg-white dark:bg-background-dark border-r border-gray-200 dark:border-gray-800 flex flex-col">
        <div className="flex h-full flex-col justify-between p-4">
          <div className="flex flex-col gap-4">
            <div className="flex items-center gap-3 px-3">
              <div 
                className="bg-center bg-no-repeat aspect-square bg-cover rounded-full size-10" 
                data-alt="BGAofis logo with an abstract geometric pattern" 
                style={{
                  backgroundImage: 'url("https://lh3.googleusercontent.com/aida-public/AB6AXuA57EO-8x2oXX4OVDxObsICXckpp2NtWPiDTEg8Vl116_shUKxdy1JI1sFNiabsSZWZM8g06eed9AdZeJpE8a-_NqVNQMYD2DELL1AqX_vEiuPh_hBT9thgSeNp7HhNsly8ZOiIeq7plBUCFwiYjpsyVGcIbJ8qN-JNfj_OES6Uh9ObSwu7Bkg1HbH6lCYxq24cNVOV_a3aMvyXChhofEbFWqwjDwH3wkcKSgZuGLu8HhgC5ewv56HD3Q3zVuHEY4zzye-z_ervC4It")'
                }}
              />
              <div className="flex flex-col">
                <h1 className="text-gray-900 dark:text-white text-base font-bold leading-normal">BGAofis</h1>
                <p className="text-gray-500 dark:text-gray-400 text-sm font-normal leading-normal">Hukuk Bürosu</p>
              </div>
            </div>
            <nav className="flex flex-col gap-2 mt-4">
              <a className="flex items-center gap-3 px-3 py-2 text-gray-600 dark:text-gray-300 hover:bg-primary/10 hover:text-primary dark:hover:text-primary rounded-lg" href="#">
                <span className="material-symbols-outlined">home</span>
                <p className="text-sm font-medium leading-normal">Anasayfa</p>
              </a>
              <a className="flex items-center gap-3 px-3 py-2 text-gray-600 dark:text-gray-300 hover:bg-primary/10 hover:text-primary dark:hover:text-primary rounded-lg" href="#">
                <span className="material-symbols-outlined">groups</span>
                <p className="text-sm font-medium leading-normal">Müvekkiller</p>
              </a>
              <a className="flex items-center gap-3 px-3 py-2 text-gray-600 dark:text-gray-300 hover:bg-primary/10 hover:text-primary dark:hover:text-primary rounded-lg" href="#">
                <span className="material-symbols-outlined">work</span>
                <p className="text-sm font-medium leading-normal">Davalar</p>
              </a>
              <a className="flex items-center gap-3 px-3 py-2 text-gray-600 dark:text-gray-300 hover:bg-primary/10 hover:text-primary dark:hover:text-primary rounded-lg" href="#">
                <span className="material-symbols-outlined">manage_accounts</span>
                <p className="text-sm font-medium leading-normal">Kullanıcılar</p>
              </a>
              <a className="flex items-center gap-3 px-3 py-2 rounded-lg bg-primary/10 text-primary dark:bg-primary/20 dark:text-primary-300 font-bold" href="#">
                <span className="material-symbols-outlined" style={{fontVariationSettings: "'FILL' 1"}}>admin_panel_settings</span>
                <p className="text-sm font-medium leading-normal">Roller & Yetkiler</p>
              </a>
              <a className="flex items-center gap-3 px-3 py-2 text-gray-600 dark:text-gray-300 hover:bg-primary/10 hover:text-primary dark:hover:text-primary rounded-lg" href="#">
                <span className="material-symbols-outlined">settings</span>
                <p className="text-sm font-medium leading-normal">Ayarlar</p>
              </a>
            </nav>
          </div>
          <div className="flex flex-col gap-1">
            <a className="flex items-center gap-3 px-3 py-2 text-gray-600 dark:text-gray-300 hover:bg-primary/10 hover:text-primary dark:hover:text-primary rounded-lg" href="#">
              <span className="material-symbols-outlined">support_agent</span>
              <p className="text-sm font-medium leading-normal">Destek</p>
            </a>
            <a className="flex items-center gap-3 px-3 py-2 text-gray-600 dark:text-gray-300 hover:bg-primary/10 hover:text-primary dark:hover:text-primary rounded-lg" href="#">
              <span className="material-symbols-outlined">logout</span>
              <p className="text-sm font-medium leading-normal">Çıkış Yap</p>
            </a>
          </div>
        </div>
      </aside>

      {/* Main Content */}
      <main className="flex-1">
        {/* Header */}
        <header className="flex h-16 items-center justify-end border-b border-gray-200 dark:border-gray-800 bg-white dark:bg-background-dark/80 px-8 sticky top-0 z-10 backdrop-blur-sm">
          <div className="flex items-center gap-4">
            <div className="relative group">
              <button className="relative flex items-center justify-center size-10 rounded-full hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-600 dark:text-gray-300">
                <span className="material-symbols-outlined">notifications</span>
                <span className="absolute top-1 right-1 flex h-2.5 w-2.5">
                  <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                  <span className="relative inline-flex rounded-full h-2.5 w-2.5 bg-red-500 border-2 border-white dark:border-gray-900"></span>
                </span>
              </button>
              <div className="absolute top-full right-0 mt-2 w-80 max-w-sm origin-top-right bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800 shadow-lg scale-95 opacity-0 group-hover:scale-100 group-hover:opacity-100 transition-all duration-200 ease-in-out invisible group-hover:visible">
                <div className="p-4 border-b border-gray-200 dark:border-gray-800">
                  <h3 className="text-base font-semibold text-gray-900 dark:text-white">Bildirimler</h3>
                </div>
                <div className="divide-y divide-gray-100 dark:divide-gray-800">
                  <a className="block p-4 hover:bg-gray-50 dark:hover:bg-gray-800/50" href="#">
                    <p className="text-sm font-medium text-gray-800 dark:text-gray-200">Bugün 10:00'da duruşmanız var</p>
                    <p className="text-xs text-gray-500 dark:text-gray-400 mt-1">Dosya: İşçilik alacağı davası</p>
                  </a>
                  <a className="block p-4 hover:bg-gray-50 dark:hover:bg-gray-800/50" href="#">
                    <p className="text-sm font-medium text-gray-800 dark:text-gray-200">Görev son tarihi yaklaşıyor</p>
                    <p className="text-xs text-gray-500 dark:text-gray-400 mt-1">Dilekçe taslağı</p>
                  </a>
                  <a className="block p-4 hover:bg-gray-50 dark:hover:bg-gray-800/50" href="#">
                    <p className="text-sm font-medium text-gray-800 dark:text-gray-200">Yeni belge eklendi</p>
                    <p className="text-xs text-gray-500 dark:text-gray-400 mt-1">Dosya: 2023/123 Esas</p>
                  </a>
                </div>
                <div className="p-2 border-t border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-900/50 rounded-b-lg">
                  <a className="block text-center text-sm font-medium text-primary hover:underline" href="#">
                    Tüm bildirimleri görüntüle
                  </a>
                </div>
              </div>
            </div>
            <div className="w-px h-6 bg-gray-200 dark:bg-gray-800 mx-2"></div>
            <div className="flex items-center gap-3">
              <div className="bg-center bg-no-repeat aspect-square bg-cover rounded-full size-10" style={{backgroundImage: 'url("https://lh3.googleusercontent.com/aida-public/AB6AXuBfh1p2g6YhUkBoYnr6cNicjITh5lzSDsEJ4oH17Ts_WnHGJYrPtjkn_d4KY0a5l9-3WK-qdUhOxGLWvlCraCoB8VByy6EmlihpA73qmGIqaujFaO6DYgq5BwFR8oyf4NCpgNYz7WIF9ZGFRoUEyCTgFtkl62orHKkxW22HZ7rCQcsw1SC1oGeNFPa3RUb8EYtVn68h4Tdq0VEr8dJAgXuNTOAe2cPxAFysF1CoVPmN7uP7pxhVfuKcELPCNuyBCqth3qDlF7_u_3Pf")'}}></div>
              <div>
                <p className="text-sm font-semibold text-gray-900 dark:text-white">Av. Ahmet Yılmaz</p>
                <p className="text-xs text-gray-500 dark:text-gray-400">Admin</p>
              </div>
            </div>
          </div>
        </header>

        {/* Page Content */}
        <div className="p-8">
          <div className="max-w-7xl mx-auto">
            <div className="flex flex-wrap justify-between items-center gap-4 mb-8">
              <div className="flex flex-col">
                <h1 className="text-gray-900 dark:text-white text-3xl font-bold leading-tight tracking-tight">Roller & Yetkiler</h1>
                <p className="text-gray-500 dark:text-gray-400 text-base font-normal leading-normal mt-1">Sistemdeki kullanıcı rollerini ve yetkilerini yönetin.</p>
              </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6">
              {/* Role Selection Sidebar */}
              <div className="md:col-span-1 lg:col-span-1">
                <div className="bg-white dark:bg-background-dark/50 rounded-lg border border-gray-200 dark:border-gray-800 shadow-sm">
                  <div className="p-4 border-b border-gray-200 dark:border-gray-800">
                    <h2 className="font-bold text-lg text-gray-900 dark:text-white">Roller</h2>
                  </div>
                  <nav className="flex flex-col p-2">
                    {roles.map((role: Role) => (
                      <button
                        key={role.id}
                        onClick={() => handleRoleChange(role.id)}
                        className={`px-4 py-2 text-sm font-medium rounded-md transition-colors ${
                          selectedRole === role.id
                            ? 'text-primary bg-primary/10 dark:bg-primary/20 font-bold'
                            : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800'
                        }`}
                      >
                        {role.name}
                      </button>
                    ))}
                  </nav>
                </div>
              </div>

              {/* Permissions Panel */}
              <div className="md:col-span-2 lg:col-span-3">
                <div className="bg-white dark:bg-background-dark/50 rounded-lg border border-gray-200 dark:border-gray-800 shadow-sm">
                  <div className="p-4 sm:p-6 border-b border-gray-200 dark:border-gray-800">
                    <h2 className="text-xl font-bold text-gray-900 dark:text-white">{currentRole.name} Yetkileri</h2>
                    <p className="text-sm text-gray-500 dark:text-gray-400 mt-1">{currentRole.name} rolüne sahip kullanıcıların sistemdeki erişim hakları.</p>
                  </div>
                  <div className="p-4 sm:p-6 space-y-6">
                    <div className="space-y-4">
                      {currentRole.permissions.map((permission) => (
                        <label key={permission.id} className="relative inline-flex items-center cursor-pointer">
                          <input
                            type="checkbox"
                            className="sr-only peer"
                            checked={permission.enabled}
                            onChange={() => handlePermissionToggle(permission.id)}
                          />
                          <div className="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary/30 dark:peer-focus:ring-primary/80 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary"></div>
                          <div className="ml-4">
                            <p className="text-sm font-medium text-gray-900 dark:text-white">{permission.name}</p>
                            <p className="text-xs font-mono text-gray-500 dark:text-gray-400">{permission.code}</p>
                          </div>
                        </label>
                      ))}
                    </div>
                  </div>
                  <div className="flex justify-end gap-3 p-4 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-200 dark:border-gray-800 rounded-b-lg">
                    <button
                      onClick={handleCancel}
                      className="rounded-lg h-10 px-4 text-sm font-bold bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700"
                    >
                      Değişiklikleri Geri Al
                    </button>
                    <button
                      onClick={handleSave}
                      disabled={!hasChanges}
                      className={`rounded-lg h-10 px-4 text-sm font-bold transition-colors ${
                        hasChanges
                          ? 'bg-primary text-white hover:bg-primary/90'
                          : 'bg-gray-300 text-gray-500 cursor-not-allowed'
                      }`}
                    >
                      Yetkileri Kaydet
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
  );
};