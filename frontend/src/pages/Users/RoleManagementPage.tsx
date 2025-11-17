import { useEffect, useState } from 'react';
import { RolesApi, Role, Permission } from '../../api/modules/users';
import { useAsyncData } from '../../hooks/useAsyncData';
import { UsersSectionLayout } from './UsersSectionLayout';

const defaultRoles: Role[] = [
  {
    id: 'admin',
    name: 'Admin',
    permissions: [
      { id: '1', name: 'Tüm Davaları Görüntüle', code: 'CASE_VIEW_ALL', description: 'Sistemdeki tüm davalara erişim', enabled: true },
      { id: '2', name: 'Atanmış Davaları Görüntüle', code: 'CASE_VIEW_ASSIGNED', description: 'Sadece atanmış davaları görüntüle', enabled: true },
      { id: '3', name: 'Dava Düzenle', code: 'CASE_EDIT', description: 'Dava bilgilerini düzenleme yetkisi', enabled: true },
      { id: '4', name: 'Yeni Dava Oluştur', code: 'CASE_CREATE', description: 'Yeni dava oluştuma yetkisi', enabled: true },
      { id: '5', name: 'Dava Sil', code: 'CASE_DELETE', description: 'Dava silme yetkisi', enabled: true },
      { id: '6', name: 'Doküman Yükle', code: 'DOC_UPLOAD', description: 'Doküman yükleme yetkisi', enabled: true },
      { id: '7', name: 'Doküman Görüntüle', code: 'DOC_VIEW', description: 'Doküman görüntüleme yetkisi', enabled: true },
      { id: '8', name: 'Müvekkil Sil', code: 'CLIENT_DELETE', description: 'Müvekkil silme yetkisi', enabled: true },
      { id: '9', name: 'Kullanıcı Yönetimi', code: 'USER_MANAGE', description: 'Kullanıcı ekleme/düzenleme/silme', enabled: true },
      { id: '10', name: 'Rol Yönetimi', code: 'ROLE_MANAGE', description: 'Rol ve yetki yönetimi', enabled: true },
    ],
  },
  {
    id: 'avukat',
    name: 'Avukat',
    permissions: [
      { id: '1', name: 'Tüm Davaları Görüntüle', code: 'CASE_VIEW_ALL', description: 'Sistemdeki tüm davalara erişim', enabled: true },
      { id: '2', name: 'Atanmış Davaları Görüntüle', code: 'CASE_VIEW_ASSIGNED', description: 'Sadece atanmış davaları görüntüle', enabled: true },
      { id: '3', name: 'Dava Düzenle', code: 'CASE_EDIT', description: 'Dava bilgilerini düzenleme yetkisi', enabled: true },
      { id: '4', name: 'Yeni Dava Oluştur', code: 'CASE_CREATE', description: 'Yeni dava oluştuma yetkisi', enabled: true },
      { id: '5', name: 'Dava Sil', code: 'CASE_DELETE', description: 'Dava silme yetkisi', enabled: false },
      { id: '6', name: 'Doküman Yükle', code: 'DOC_UPLOAD', description: 'Doküman yükleme yetkisi', enabled: true },
      { id: '7', name: 'Doküman Görüntüle', code: 'DOC_VIEW', description: 'Doküman görüntüleme yetkisi', enabled: true },
      { id: '8', name: 'Müvekkil Sil', code: 'CLIENT_DELETE', description: 'Müvekkil silme yetkisi', enabled: false },
      { id: '9', name: 'Kullanıcı Yönetimi', code: 'USER_MANAGE', description: 'Kullanıcı ekleme/düzenleme/silme', enabled: false },
      { id: '10', name: 'Rol Yönetimi', code: 'ROLE_MANAGE', description: 'Rol ve yetki yönetimi', enabled: false },
    ],
  },
  {
    id: 'stajyer',
    name: 'Stajyer',
    permissions: [
      { id: '1', name: 'Tüm Davaları Görüntüle', code: 'CASE_VIEW_ALL', description: 'Sistemdeki tüm davalara erişim', enabled: false },
      { id: '2', name: 'Atanmış Davaları Görüntüle', code: 'CASE_VIEW_ASSIGNED', description: 'Sadece atanmış davaları görüntüle', enabled: true },
      { id: '3', name: 'Dava Düzenle', code: 'CASE_EDIT', description: 'Dava bilgilerini düzenleme yetkisi', enabled: false },
      { id: '4', name: 'Yeni Dava Oluştur', code: 'CASE_CREATE', description: 'Yeni dava oluştuma yetkisi', enabled: false },
      { id: '5', name: 'Dava Sil', code: 'CASE_DELETE', description: 'Dava silme yetkisi', enabled: false },
      { id: '6', name: 'Doküman Yükle', code: 'DOC_UPLOAD', description: 'Doküman yükleme yetkisi', enabled: true },
      { id: '7', name: 'Doküman Görüntüle', code: 'DOC_VIEW', description: 'Doküman görüntüleme yetkisi', enabled: true },
      { id: '8', name: 'Müvekkil Sil', code: 'CLIENT_DELETE', description: 'Müvekkil silme yetkisi', enabled: false },
      { id: '9', name: 'Kullanıcı Yönetimi', code: 'USER_MANAGE', description: 'Kullanıcı ekleme/düzenleme/silme', enabled: false },
      { id: '10', name: 'Rol Yönetimi', code: 'ROLE_MANAGE', description: 'Rol ve yetki yönetimi', enabled: false },
    ],
  },
  {
    id: 'asistan',
    name: 'Asistan',
    permissions: [
      { id: '1', name: 'Tüm Davaları Görüntüle', code: 'CASE_VIEW_ALL', description: 'Sistemdeki tüm davalara erişim', enabled: true },
      { id: '2', name: 'Atanmış Davaları Görüntüle', code: 'CASE_VIEW_ASSIGNED', description: 'Sadece atanmış davaları görüntüle', enabled: true },
      { id: '3', name: 'Dava Düzenle', code: 'CASE_EDIT', description: 'Dava bilgilerini düzenleme yetkisi', enabled: false },
      { id: '4', name: 'Yeni Dava Oluştur', code: 'CASE_CREATE', description: 'Yeni dava oluştuma yetkisi', enabled: false },
      { id: '5', name: 'Dava Sil', code: 'CASE_DELETE', description: 'Dava silme yetkisi', enabled: false },
      { id: '6', name: 'Doküman Yükle', code: 'DOC_UPLOAD', description: 'Doküman yükleme yetkisi', enabled: true },
      { id: '7', name: 'Doküman Görüntüle', code: 'DOC_VIEW', description: 'Doküman görüntüleme yetkisi', enabled: true },
      { id: '8', name: 'Müvekkil Sil', code: 'CLIENT_DELETE', description: 'Müvekkil silme yetkisi', enabled: false },
      { id: '9', name: 'Kullanıcı Yönetimi', code: 'USER_MANAGE', description: 'Kullanıcı ekleme/düzenleme/silme', enabled: false },
      { id: '10', name: 'Rol Yönetimi', code: 'ROLE_MANAGE', description: 'Rol ve yetki yönetimi', enabled: false },
    ],
  },
  {
    id: 'muhasebe',
    name: 'Muhasebe',
    permissions: [
      { id: '1', name: 'Tüm Davaları Görüntüle', code: 'CASE_VIEW_ALL', description: 'Sistemdeki tüm davalara erişim', enabled: true },
      { id: '2', name: 'Atanmış Davaları Görüntüle', code: 'CASE_VIEW_ASSIGNED', description: 'Sadece atanmış davaları görüntüle', enabled: true },
      { id: '3', name: 'Dava Düzenle', code: 'CASE_EDIT', description: 'Dava bilgilerini düzenleme yetkisi', enabled: false },
      { id: '4', name: 'Yeni Dava Oluştur', code: 'CASE_CREATE', description: 'Yeni dava oluştuma yetkisi', enabled: false },
      { id: '5', name: 'Dava Sil', code: 'CASE_DELETE', description: 'Dava silme yetkisi', enabled: false },
      { id: '6', name: 'Doküman Yükle', code: 'DOC_UPLOAD', description: 'Doküman yükleme yetkisi', enabled: true },
      { id: '7', name: 'Doküman Görüntüle', code: 'DOC_VIEW', description: 'Doküman görüntüleme yetkisi', enabled: true },
      { id: '8', name: 'Müvekkil Sil', code: 'CLIENT_DELETE', description: 'Müvekkil silme yetkisi', enabled: false },
      { id: '9', name: 'Kullanıcı Yönetimi', code: 'USER_MANAGE', description: 'Kullanıcı ekleme/düzenleme/silme', enabled: false },
      { id: '10', name: 'Rol Yönetimi', code: 'ROLE_MANAGE', description: 'Rol ve yetki yönetimi', enabled: false },
    ],
  },
];

export const RoleManagementPage = () => {
  const [selectedRole, setSelectedRole] = useState('avukat');
  const [currentRole, setCurrentRole] = useState<Role>(() => ({
    ...defaultRoles[1],
    permissions: defaultRoles[1].permissions.map((permission) => ({ ...permission })),
  }));
  const [hasChanges, setHasChanges] = useState(false);
  const { data: rolesData, isLoading, refetch } = useAsyncData(['roles'], RolesApi.getRoles);
  const roles = rolesData || defaultRoles;

  useEffect(() => {
    const matched = roles.find((role: Role) => role.id === selectedRole) ?? roles[0];
    setCurrentRole({
      ...matched,
      permissions: matched.permissions.map((permission: Permission) => ({ ...permission })),
    });
    setHasChanges(false);
  }, [roles, selectedRole]);

  const handlePermissionToggle = (permissionId: string) => {
    setCurrentRole((prev) => ({
      ...prev,
      permissions: prev.permissions.map((permission: Permission) =>
        permission.id === permissionId ? { ...permission, enabled: !permission.enabled } : permission
      ),
    }));
    setHasChanges(true);
  };

  const handleSave = async () => {
    try {
      const permissionsUpdate = {
        roleId: currentRole.id,
        permissions: currentRole.permissions.map((permission: Permission) => ({ id: permission.id, enabled: permission.enabled })),
      };
      await RolesApi.updateRolePermissions(currentRole.id, permissionsUpdate);
      await refetch();
      setHasChanges(false);
      console.log('Role permissions saved successfully:', currentRole);
    } catch (error) {
      console.error('Error saving role permissions:', error);
    }
  };

  const handleCancel = () => {
    const original = roles.find((role: Role) => role.id === selectedRole) ?? roles[0];
    setCurrentRole({
      ...original,
      permissions: original.permissions.map((permission: Permission) => ({ ...permission })),
    });
    setHasChanges(false);
  };

  if (isLoading) return <p className="p-8 text-sm text-gray-500">Roller yükleniyor...</p>;

  return (
    <UsersSectionLayout activeTab="roles">
      <div className="space-y-6">
        <div className="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
          <p className="text-sm text-gray-500">Sistemdeki rollerin izinlerini yönetin.</p>
          <h1 className="mt-2 text-2xl font-bold text-gray-900">Roller & Yetkiler</h1>
        </div>
        <div className="grid gap-6 lg:grid-cols-[220px,1fr]">
          <div className="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div className="border-b border-gray-200 px-5 py-4">
              <h2 className="text-lg font-semibold text-gray-900">Roller</h2>
              <p className="text-sm text-gray-500 mt-1">Bir role tıklayarak yetkilerini düzenleyin.</p>
            </div>
            <div className="flex flex-col gap-2 p-3">
              {roles.map((role: Role) => (
                <button
                  key={role.id}
                  onClick={() => setSelectedRole(role.id)}
                  className={`flex w-full items-center justify-between rounded-xl px-4 py-3 text-sm font-medium transition ${
                    selectedRole === role.id
                      ? 'bg-primary/10 text-primary'
                      : 'text-gray-600 hover:bg-gray-50 hover:text-primary'
                  }`}
                >
                  <span>{role.name}</span>
                  <span className="text-xs font-normal uppercase tracking-wide text-gray-400">{role.permissions.length} yetki</span>
                </button>
              ))}
            </div>
          </div>
          <div className="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div className="border-b border-gray-200 px-6 py-5">
              <h2 className="text-xl font-semibold text-gray-900">{currentRole.name} Yetkileri</h2>
              <p className="text-sm text-gray-500 mt-1">
                {currentRole.name} rolünün izinlerini açıp kapatarak sistem erişimini tanımlayın.
              </p>
            </div>
            <div className="space-y-5 p-6">
              {currentRole.permissions.map((permission: Permission) => (
                <label key={permission.id} className="flex items-center gap-4">
                  <input
                    type="checkbox"
                    checked={permission.enabled}
                    onChange={() => handlePermissionToggle(permission.id)}
                    className="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
                  />
                  <div>
                    <p className="text-sm font-semibold text-gray-900">{permission.name}</p>
                    <p className="text-xs text-gray-500">{permission.code}</p>
                  </div>
                </label>
              ))}
            </div>
            <div className="flex justify-end gap-3 border-t border-gray-200 px-6 py-4 bg-gray-50">
              <button
                type="button"
                onClick={handleCancel}
                className="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-bold text-gray-700 hover:bg-gray-50"
              >
                Değişiklikleri Geri Al
              </button>
              <button
                type="button"
                onClick={handleSave}
                disabled={!hasChanges}
                className={`rounded-lg px-4 py-2 text-sm font-bold text-white ${
                  hasChanges ? 'bg-primary hover:bg-primary/90' : 'bg-gray-300 text-gray-500 cursor-not-allowed'
                }`}
              >
                Yetkileri Kaydet
              </button>
            </div>
          </div>
        </div>
      </div>
    </UsersSectionLayout>
  );
};
