import { useMemo, useState } from 'react';
import { UsersApi, User, RolesApi, Role } from '../../api/modules/users';
import { useAsyncData } from '../../hooks/useAsyncData';
import { UsersSectionLayout } from './UsersSectionLayout';

export const UserManagementPage = () => {
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [isSaving, setIsSaving] = useState(false);
  const [saveError, setSaveError] = useState<string | null>(null);
  const [formData, setFormData] = useState({
    fullName: '',
    email: '',
    password: '',
    roles: [] as string[],
    status: 'active' as 'active' | 'inactive',
  });

  const {
    data: usersData,
    isLoading,
    refetch: refetchUsers,
  } = useAsyncData<User[]>(['users'], UsersApi.getUsers);
  const { data: rolesData } = useAsyncData<Role[]>(['roles'], RolesApi.getRoles);

  const defaultUsers: User[] = [
    {
      id: '1',
      fullName: 'Ahmet Yılmaz',
      email: 'ahmet.yilmaz@bgaofis.com',
      status: 'active',
      roles: ['Avukat', 'Yönetici'],
    },
  ];

  const users = usersData || defaultUsers;

  const availableRoles = useMemo(() => {
    const fromApi = (rolesData ?? []).map((role) => ({ id: role.id, name: role.name }));

    if (fromApi.length > 0) {
      return fromApi;
    }

    return [
      { id: 'admin', name: 'Admin' },
      { id: 'lawyer', name: 'Avukat' },
      { id: 'intern', name: 'Stajyer' },
      { id: 'assistant', name: 'Asistan' },
      { id: 'accounting', name: 'Muhasebe' },
    ];
  }, [rolesData]);

  const getStatusBadge = (status: 'active' | 'inactive') => {
    if (status === 'active') {
      return (
        <span className="inline-flex items-center gap-1.5 rounded-full bg-green-100 dark:bg-green-900/40 px-2.5 py-0.5 text-xs font-medium text-green-800 dark:text-green-300">
          <span className="size-2 rounded-full bg-green-500" aria-hidden />
          Aktif
        </span>
      );
    }

    return (
      <span className="inline-flex items-center gap-1.5 rounded-full bg-gray-100 dark:bg-gray-700/40 px-2.5 py-0.5 text-xs font-medium text-gray-800 dark:text-gray-300">
        <span className="size-2 rounded-full bg-gray-500" aria-hidden />
        Pasif
      </span>
    );
  };

  const getRoleBadge = (role: string) => (
    <span className="inline-flex items-center rounded-md bg-primary/10 dark:bg-primary/20 px-2 py-1 text-xs font-medium text-primary dark:text-primary-300">
      {role}
    </span>
  );

  const handleEditUser = (userId: string) => {
    // Düzenleme akışı daha sonra eklenecek
    // eslint-disable-next-line no-console
    console.log('Edit user:', userId);
  };

  const handleAddUser = async () => {
    try {
      setIsSaving(true);
      setSaveError(null);
      await UsersApi.createUser(formData);
      await refetchUsers();
      setIsModalOpen(false);
      setFormData({
        fullName: '',
        email: '',
        password: '',
        roles: [],
        status: 'active',
      });
    } catch (error) {
      setSaveError('Kullanıcı oluşturulurken bir hata oluştu.');
      // eslint-disable-next-line no-console
      console.error('Error creating user:', error);
    } finally {
      setIsSaving(false);
    }
  };

  const isSaveDisabled =
    isSaving ||
    !formData.fullName.trim() ||
    !formData.email.trim() ||
    !formData.password.trim() ||
    formData.roles.length === 0;

  return (
    <>
      <UsersSectionLayout activeTab="users">
        <div className="flex flex-wrap justify-between items-center gap-4 mb-8">
          <div className="flex flex-col">
            <h1 className="text-gray-900 dark:text-white text-3xl font-bold leading-tight tracking-tight">
              Kullanıcılar
            </h1>
            <p className="text-gray-500 dark:text-gray-400 text-base font-normal leading-normal mt-1">
              Sistemdeki kullanıcıları yönetin ve yeni kullanıcılar ekleyin.
            </p>
          </div>
          <button
            type="button"
            onClick={() => setIsModalOpen(true)}
            className="flex items-center justify-center gap-2 overflow-hidden rounded-lg h-10 px-4 bg-primary text-white text-sm font-bold leading-normal tracking-wide shadow-sm hover:bg-primary/90 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary"
          >
            <span className="material-symbols-outlined">add</span>
            <span className="truncate">Yeni Kullanıcı Ekle</span>
          </button>
        </div>

        <div className="bg-white dark:bg-background-dark/50 rounded-lg border border-gray-200 dark:border-gray-800 shadow-sm overflow-hidden">
          <div className="overflow-x-auto">
            <table className="w-full text-sm">
              <thead className="bg-gray-50 dark:bg-gray-800/50">
                <tr>
                  <th
                    className="px-6 py-3 text-left font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider"
                    scope="col"
                  >
                    Ad Soyad
                  </th>
                  <th
                    className="px-6 py-3 text-left font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider"
                    scope="col"
                  >
                    E-posta
                  </th>
                  <th
                    className="px-6 py-3 text-left font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider"
                    scope="col"
                  >
                    Durum
                  </th>
                  <th
                    className="px-6 py-3 text-left font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider"
                    scope="col"
                  >
                    Roller
                  </th>
                  <th
                    className="px-6 py-3 text-right font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider"
                    scope="col"
                  >
                    İşlemler
                  </th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-200 dark:divide-gray-800">
                {isLoading ? (
                  <tr>
                    <td
                      colSpan={5}
                      className="px-6 py-6 text-center text-sm text-gray-500 dark:text-gray-400"
                    >
                      Kullanıcılar yükleniyor...
                    </td>
                  </tr>
                ) : (
                  users.map((user: User) => (
                    <tr key={user.id}>
                      <td className="px-6 py-4 whitespace-nowrap font-medium text-gray-900 dark:text-white">
                        {user.fullName}
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap text-gray-500 dark:text-gray-400">
                        {user.email}
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap">
                        {getStatusBadge(user.status)}
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap">
                        <div className="flex items-center gap-2 flex-wrap">
                          {(user.roles || []).map((role) => (
                            <span key={role}>{getRoleBadge(role)}</span>
                          ))}
                        </div>
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap text-right font-medium">
                        <button
                          type="button"
                          onClick={() => handleEditUser(user.id)}
                          className="text-primary hover:text-primary/80 dark:text-primary-400 dark:hover:text-primary-300"
                        >
                          Düzenle
                        </button>
                      </td>
                    </tr>
                  ))
                )}
              </tbody>
            </table>
          </div>
        </div>
      </UsersSectionLayout>

      {isModalOpen && (
        <div
          aria-labelledby="modal-title"
          aria-modal="true"
          className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
          role="dialog"
        >
          <div className="bg-white dark:bg-gray-900 rounded-xl shadow-xl w-full max-w-lg m-4">
            <div className="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-800">
              <h2
                className="text-lg font-bold text-gray-900 dark:text-white"
                id="modal-title"
              >
                Yeni Kullanıcı Ekle
              </h2>
              <button
                type="button"
                onClick={() => setIsModalOpen(false)}
                className="p-1 rounded-full hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-500 dark:text-gray-400"
              >
                <span className="material-symbols-outlined">close</span>
              </button>
            </div>

            <div className="p-6 space-y-6">
              <div>
                <label
                  className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
                  htmlFor="fullName"
                >
                  Ad Soyad
                </label>
                <input
                  id="fullName"
                  type="text"
                  value={formData.fullName}
                  onChange={(e) => setFormData({ ...formData, fullName: e.target.value })}
                  placeholder="Ahmet Yılmaz"
                  className="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white shadow-sm focus:border-primary focus:ring-primary text-sm"
                />
              </div>

              <div>
                <label
                  className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
                  htmlFor="email"
                >
                  E-posta Adresi
                </label>
                <input
                  id="email"
                  type="email"
                  value={formData.email}
                  onChange={(e) => setFormData({ ...formData, email: e.target.value })}
                  placeholder="ahmet.yilmaz@bgaofis.com"
                  className="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white shadow-sm focus:border-primary focus:ring-primary text-sm"
                />
              </div>

              <div>
                <label
                  className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
                  htmlFor="password"
                >
                  Şifre
                </label>
                <input
                  id="password"
                  type="password"
                  value={formData.password}
                  onChange={(e) => setFormData({ ...formData, password: e.target.value })}
                  placeholder="••••••••"
                  className="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white shadow-sm focus:border-primary focus:ring-primary text-sm"
                />
                <p className="text-xs text-gray-500 dark:text-gray-400 mt-1">
                  Düzenleme yaparken değiştirmek istemiyorsanız boş bırakın.
                </p>
              </div>

              <div>
                <label
                  className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
                  htmlFor="roles"
                >
                  Roller
                </label>
                <select
                  id="roles"
                  multiple
                  value={formData.roles}
                  onChange={(e) => {
                    const selectedRoles = Array.from(
                      e.target.selectedOptions,
                      (option) => option.value,
                    );
                    setFormData({ ...formData, roles: selectedRoles });
                  }}
                  className="w-full rounded border-gray-300 bg-white text-gray-900 shadow-sm focus:border-primary focus:ring-primary text-sm"
                >
                  {availableRoles.map((role) => (
                    <option key={role.id} value={role.id}>
                      {role.name}
                    </option>
                  ))}
                </select>
              </div>

              <div>
                <p className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Durum
                </p>
                <div className="flex items-center">
                  <input
                    id="status-active"
                    name="status"
                    type="radio"
                    className="h-4 w-4 border-gray-300 text-primary focus:ring-primary"
                    checked={formData.status === 'active'}
                    onChange={() => setFormData({ ...formData, status: 'active' })}
                  />
                  <label
                    className="ml-2 block text-sm text-gray-900 dark:text-gray-100"
                    htmlFor="status-active"
                  >
                    Aktif
                  </label>
                </div>
                <div className="flex items-center mt-2">
                  <input
                    id="status-inactive"
                    name="status"
                    type="radio"
                    className="h-4 w-4 border-gray-300 text-primary focus:ring-primary"
                    checked={formData.status === 'inactive'}
                    onChange={() => setFormData({ ...formData, status: 'inactive' })}
                  />
                  <label
                    className="ml-2 block text-sm text-gray-900 dark:text-gray-100"
                    htmlFor="status-inactive"
                  >
                    Pasif
                  </label>
                </div>
              </div>

              {saveError && (
                <p className="text-xs text-red-600 dark:text-red-400">{saveError}</p>
              )}
            </div>

            <div className="flex justify-end gap-3 p-4 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-200 dark:border-gray-800 rounded-b-xl">
              <button
                type="button"
                onClick={() => setIsModalOpen(false)}
                className="rounded-lg h-10 px-4 text-sm font-bold bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700"
              >
                İptal
              </button>
              <button
                type="button"
                onClick={handleAddUser}
                disabled={isSaveDisabled}
                className={`rounded-lg h-10 px-4 text-sm font-bold text-white ${
                  isSaveDisabled
                    ? 'bg-gray-300 cursor-not-allowed'
                    : 'bg-primary hover:bg-primary/90'
                }`}
              >
                {isSaving ? 'Kaydediliyor...' : 'Kaydet'}
              </button>
            </div>
          </div>
        </div>
      )}
    </>
  );
};
