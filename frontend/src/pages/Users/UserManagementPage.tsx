import { useMemo, useState } from 'react';
import { UsersApi, User, RolesApi, Role } from '../../api/modules/users';
import { useAsyncData } from '../../hooks/useAsyncData';
import { UsersSectionLayout } from './UsersSectionLayout';

export const UserManagementPage = () => {
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [formData, setFormData] = useState({
    fullName: '',
    email: '',
    password: '',
    roles: [] as string[],
    status: 'active' as 'active' | 'inactive',
  });

  const { data: usersData, isLoading } = useAsyncData<User[]>(['users'], UsersApi.getUsers);
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

  const availableRoles = useMemo(
    () => (rolesData ?? []).map((role) => ({ id: role.id, name: role.name })),
    [rolesData],
  );

  const getStatusBadge = (status: 'active' | 'inactive') => {
    if (status === 'active') {
      return (
        <span className="inline-flex items-center gap-1.5 rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
          <span className="size-2 rounded-full bg-green-500" aria-hidden />
          Aktif
        </span>
      );
    }

    return (
      <span className="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600">
        <span className="size-2 rounded-full bg-gray-500" aria-hidden />
        Pasif
      </span>
    );
  };

  const getRoleBadge = (role: string) => (
    <span className="inline-flex items-center rounded-md bg-primary/10 px-2 py-1 text-xs font-medium text-primary">
      {role}
    </span>
  );

  const handleEditUser = (userId: string) => {
    console.log('Edit user:', userId);
  };

  const handleAddUser = async () => {
    try {
      await UsersApi.createUser(formData);
      setIsModalOpen(false);
      setFormData({
        fullName: '',
        email: '',
        password: '',
        roles: [],
        status: 'active',
      });
    } catch (error) {
      console.error('Error creating user:', error);
    }
  };

  return (
    <>
      <UsersSectionLayout activeTab="users">
        <div className="flex flex-wrap items-center justify-between gap-4">
          <div>
            <h1 className="text-3xl font-bold text-gray-900">Kullanıcılar</h1>
            <p className="text-sm text-gray-500 mt-1">
              Sistemdeki kullanıcıları yönetip yeni üyeler ekleyin.
            </p>
          </div>
          <button
            onClick={() => setIsModalOpen(true)}
            className="flex items-center justify-center gap-2 rounded-lg bg-primary px-4 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-primary/90 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary"
          >
            <span className="material-symbols-outlined text-base">add</span>
            Yeni Kullanıcı Ekle
          </button>
        </div>

        <div className="rounded-2xl border border-gray-200 bg-white shadow-sm mt-6">
          <div className="overflow-x-auto">
            <table className="w-full text-sm">
              <thead className="bg-gray-50">
                <tr>
                  <th className="px-6 py-3 text-left font-medium text-gray-600 uppercase tracking-wider">
                    Ad Soyad
                  </th>
                  <th className="px-6 py-3 text-left font-medium text-gray-600 uppercase tracking-wider">
                    E-posta
                  </th>
                  <th className="px-6 py-3 text-left font-medium text-gray-600 uppercase tracking-wider">
                    Durum
                  </th>
                  <th className="px-6 py-3 text-left font-medium text-gray-600 uppercase tracking-wider">
                    Roller
                  </th>
                  <th className="px-6 py-3 text-right font-medium text-gray-600 uppercase tracking-wider">
                    İşlemler
                  </th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-200">
                {users.map((user: User) => (
                  <tr key={user.id}>
                    <td className="px-6 py-4 whitespace-nowrap font-medium text-gray-900">
                      {user.fullName}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-gray-500">{user.email}</td>
                    <td className="px-6 py-4 whitespace-nowrap">{getStatusBadge(user.status)}</td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      <div className="flex flex-wrap gap-2">
                        {user.roles.map((role: string) => (
                          <span key={role}>{getRoleBadge(role)}</span>
                        ))}
                      </div>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-right font-medium">
                      <button
                        onClick={() => handleEditUser(user.id)}
                        className="text-primary hover:text-primary/80"
                      >
                        Düzenle
                      </button>
                    </td>
                  </tr>
                ))}
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
          <div className="w-full max-w-lg rounded-2xl bg-white shadow-xl">
            <div className="flex items-center justify-between border-b border-gray-200 px-6 py-4">
              <h2 className="text-lg font-bold text-gray-900" id="modal-title">
                Yeni Kullanıcı Ekle
              </h2>
              <button
                onClick={() => setIsModalOpen(false)}
                className="p-1 rounded-full text-gray-500 hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary"
              >
                <span className="material-symbols-outlined">close</span>
              </button>
            </div>
            <div className="space-y-6 p-6">
              <div>
                <label className="block text-sm font-medium text-gray-700" htmlFor="fullName">
                  Ad Soyad
                </label>
                <input
                  id="fullName"
                  type="text"
                  value={formData.fullName}
                  onChange={(e) => setFormData({ ...formData, fullName: e.target.value })}
                  placeholder="Ahmet Yılmaz"
                  className="mt-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary"
                />
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700" htmlFor="email">
                  E-posta Adresi
                </label>
                <input
                  id="email"
                  type="email"
                  value={formData.email}
                  onChange={(e) => setFormData({ ...formData, email: e.target.value })}
                  placeholder="ahmet.yilmaz@bgaofis.com"
                  className="mt-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary"
                />
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700" htmlFor="password">
                  Şifre
                </label>
                <input
                  id="password"
                  type="password"
                  value={formData.password}
                  onChange={(e) => setFormData({ ...formData, password: e.target.value })}
                  placeholder="Varsayılan şifreyi kullanmak için boş bırakın"
                  className="mt-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary"
                />
                <p className="text-xs text-gray-500 mt-1">
                  Boş bırakmak varsayılan şifreyi kullanır.
                </p>
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700" htmlFor="roles">
                  Roller
                </label>
                <select
                  id="roles"
                  multiple
                  value={formData.roles}
                  onChange={(e) => {
                    const selectedRoles = Array.from(e.target.selectedOptions, (option) => option.value);
                    setFormData({ ...formData, roles: selectedRoles });
                  }}
                  className="mt-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary"
                >
                  {availableRoles.map((role) => (
                    <option key={role.id} value={role.id}>
                      {role.name}
                    </option>
                  ))}
                </select>
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700">Durum</label>
                <div className="mt-2 flex items-center gap-6">
                  <label className="flex items-center gap-2 text-sm text-gray-900">
                    <input
                      className="h-4 w-4 rounded-full border-gray-300 text-primary focus:ring-primary"
                      type="radio"
                      name="status"
                      checked={formData.status === 'active'}
                      onChange={() => setFormData({ ...formData, status: 'active' })}
                    />
                    Aktif
                  </label>
                  <label className="flex items-center gap-2 text-sm text-gray-900">
                    <input
                      className="h-4 w-4 rounded-full border-gray-300 text-primary focus:ring-primary"
                      type="radio"
                      name="status"
                      checked={formData.status === 'inactive'}
                      onChange={() => setFormData({ ...formData, status: 'inactive' })}
                    />
                    Pasif
                  </label>
                </div>
              </div>
            </div>
            <div className="flex justify-end gap-3 border-t border-gray-200 px-6 py-4 bg-gray-50">
              <button
                type="button"
                onClick={() => setIsModalOpen(false)}
                className="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-bold text-gray-700 hover:bg-gray-50"
              >
                İptal
              </button>
              <button
                type="button"
                onClick={handleAddUser}
                className="rounded-lg bg-primary px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-primary/90"
              >
                Kaydet
              </button>
            </div>
          </div>
        </div>
      )}
    </>
  );
};

