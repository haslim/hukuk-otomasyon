import { useState } from 'react';
import { UsersApi, User } from '../../api/modules/users';
import { useAsyncData } from '../../hooks/useAsyncData';

export const UserManagementPage = () => {
  const [isModalOpen, setIsModalOpen] = useState(false);
  const { data: usersData, isLoading } = useAsyncData(['users'], UsersApi.getUsers);

  if (isLoading) return <p>Kullanıcılar yükleniyor...</p>;

  const defaultUsers: User[] = [
    {
      id: '1',
      fullName: 'Ahmet Yılmaz',
      email: 'ahmet.yilmaz@bgaofis.com',
      status: 'active',
      roles: ['Avukat', 'Yönetici']
    },
    {
      id: '2',
      fullName: 'Ayşe Kaya',
      email: 'ayse.kaya@bgaofis.com',
      status: 'active',
      roles: ['Asistan']
    },
    {
      id: '3',
      fullName: 'Mehmet Öztürk',
      email: 'mehmet.ozturk@bgaofis.com',
      status: 'inactive',
      roles: ['Stajyer']
    },
    {
      id: '4',
      fullName: 'Fatma Demir',
      email: 'fatma.demir@bgaofis.com',
      status: 'active',
      roles: ['Muhasebe']
    }
  ];

  const users = usersData || defaultUsers;

  const [formData, setFormData] = useState({
    fullName: '',
    email: '',
    password: '',
    roles: [] as string[],
    status: 'active' as 'active' | 'inactive'
  });

  const getStatusBadge = (status: 'active' | 'inactive') => {
    switch (status) {
      case 'active':
        return (
          <span className="inline-flex items-center gap-1.5 rounded-full bg-green-100 dark:bg-green-900/40 px-2.5 py-0.5 text-xs font-medium text-green-800 dark:text-green-300">
            <span className="size-2 rounded-full bg-green-500"></span>
            Aktif
          </span>
        );
      case 'inactive':
        return (
          <span className="inline-flex items-center gap-1.5 rounded-full bg-gray-100 dark:bg-gray-700/40 px-2.5 py-0.5 text-xs font-medium text-gray-800 dark:text-gray-300">
            <span className="size-2 rounded-full bg-gray-500"></span>
            Pasif
          </span>
        );
      default:
        return null;
    }
  };

  const getRoleBadge = (role: string) => {
    return (
      <span className="inline-flex items-center rounded-md bg-primary/10 dark:bg-primary/20 px-2 py-1 text-xs font-medium text-primary dark:text-primary-300">
        {role}
      </span>
    );
  };

  const handleEditUser = (userId: string) => {
    console.log('Edit user:', userId);
  };

  const handleAddUser = () => {
    console.log('Add new user with data:', formData);
    setIsModalOpen(false);
    setFormData({
      fullName: '',
      email: '',
      password: '',
      roles: [],
      status: 'active'
    });
  };

  const handleRoleChange = (role: string) => {
    setFormData(prev => ({
      ...prev,
      roles: prev.roles.includes(role) 
        ? prev.roles.filter(r => r !== role)
        : [...prev.roles, role]
    }));
  };

  return (
    <div className="flex min-h-screen">
      {/* SideNavBar Component */}
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
              <a className="flex items-center gap-3 px-3 py-2 rounded-lg bg-primary/10 text-primary dark:bg-primary/20 dark:text-primary-300 font-bold" href="#">
                <span className="material-symbols-outlined" style={{fontVariationSettings: "'FILL' 1"}}>manage_accounts</span>
                <p className="text-sm font-medium leading-normal">Kullanıcılar</p>
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
      <main className="flex-1 p-8">
        <div className="max-w-7xl mx-auto">
          {/* PageHeading Component */}
          <div className="flex flex-wrap justify-between items-center gap-4 mb-8">
            <div className="flex flex-col">
              <h1 className="text-gray-900 dark:text-white text-3xl font-bold leading-tight tracking-tight">Kullanıcılar</h1>
              <p className="text-gray-500 dark:text-gray-400 text-base font-normal leading-normal mt-1">Sistemdeki kullanıcıları yönetin ve yeni kullanıcılar ekleyin.</p>
            </div>
            <button 
              onClick={() => setIsModalOpen(true)}
              className="flex items-center justify-center gap-2 overflow-hidden rounded-lg h-10 px-4 bg-primary text-white text-sm font-bold leading-normal tracking-wide shadow-sm hover:bg-primary/90 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary"
            >
              <span className="material-symbols-outlined">add</span>
              <span className="truncate">Yeni Kullanıcı Ekle</span>
            </button>
          </div>

          {/* Table Component */}
          <div className="bg-white dark:bg-background-dark/50 rounded-lg border border-gray-200 dark:border-gray-800 shadow-sm overflow-hidden">
            <div className="overflow-x-auto">
              <table className="w-full text-sm">
                <thead className="bg-gray-50 dark:bg-gray-800/50">
                  <tr>
                    <th className="px-6 py-3 text-left font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider" scope="col">Ad Soyad</th>
                    <th className="px-6 py-3 text-left font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider" scope="col">E-posta</th>
                    <th className="px-6 py-3 text-left font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider" scope="col">Durum</th>
                    <th className="px-6 py-3 text-left font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider" scope="col">Roller</th>
                    <th className="px-6 py-3 text-right font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider" scope="col">İşlemler</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-gray-200 dark:divide-gray-800">
                  {users.map((user: User) => (
                    <tr key={user.id}>
                      <td className="px-6 py-4 whitespace-nowrap font-medium text-gray-900 dark:text-white">{user.fullName}</td>
                      <td className="px-6 py-4 whitespace-nowrap text-gray-500 dark:text-gray-400">{user.email}</td>
                      <td className="px-6 py-4 whitespace-nowrap">
                        {getStatusBadge(user.status)}
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap">
                        <div className="flex items-center gap-2">
                          {user.roles.map((role: string) => (
                            <span key={role}>{getRoleBadge(role)}</span>
                          ))}
                        </div>
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap text-right font-medium">
                        <button 
                          onClick={() => handleEditUser(user.id)}
                          className="text-primary hover:text-primary/80 dark:text-primary-400 dark:hover:text-primary-300"
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
        </div>
      </main>

      {/* Modal Component */}
      {isModalOpen && (
        <div 
          aria-labelledby="modal-title" 
          aria-modal="true" 
          className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm" 
          role="dialog"
        >
          <div className="bg-white dark:bg-gray-900 rounded-xl shadow-xl w-full max-w-lg m-4">
            <div className="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-800">
              <h2 className="text-lg font-bold text-gray-900 dark:text-white" id="modal-title">Yeni Kullanıcı Ekle</h2>
              <button 
                onClick={() => setIsModalOpen(false)}
                className="p-1 rounded-full hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-500 dark:text-gray-400"
              >
                <span className="material-symbols-outlined">close</span>
              </button>
            </div>
            <div className="p-6 space-y-6">
              <div>
                <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" htmlFor="fullName">Ad Soyad</label>
                <input 
                  className="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white shadow-sm focus:border-primary focus:ring-primary text-sm" 
                  id="fullName" 
                  placeholder="Ahmet Yılmaz" 
                  type="text"
                  value={formData.fullName}
                  onChange={(e) => setFormData({...formData, fullName: e.target.value})}
                />
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" htmlFor="email">E-posta Adresi</label>
                <input 
                  className="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white shadow-sm focus:border-primary focus:ring-primary text-sm" 
                  id="email" 
                  placeholder="ahmet.yilmaz@bgaofis.com" 
                  type="email"
                  value={formData.email}
                  onChange={(e) => setFormData({...formData, email: e.target.value})}
                />
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" htmlFor="password">Şifre</label>
                <input 
                  className="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white shadow-sm focus:border-primary focus:ring-primary text-sm" 
                  id="password" 
                  placeholder="•••••" 
                  type="password"
                  value={formData.password}
                  onChange={(e) => setFormData({...formData, password: e.target.value})}
                />
                <p className="text-xs text-gray-500 dark:text-gray-400 mt-1">Düzenleme yaparken değiştirmek istemiyorsanız boş bırakın.</p>
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" htmlFor="roles">Roller</label>
                <select 
                  className="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white shadow-sm focus:border-primary focus:ring-primary text-sm" 
                  id="roles" 
                  multiple
                  value={formData.roles}
                  onChange={(e) => {
                    const selectedRoles = Array.from(e.target.selectedOptions, option => option.value) as string[];
                    setFormData({...formData, roles: selectedRoles});
                  }}
                >
                  <option value="Avukat">Avukat</option>
                  <option value="Yönetici">Yönetici</option>
                  <option value="Asistan">Asistan</option>
                  <option value="Stajyer">Stajyer</option>
                  <option value="Muhasebe">Muhasebe</option>
                </select>
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Durum</label>
                <div className="flex items-center">
                  <input 
                    checked={formData.status === 'active'}
                    className="h-4 w-4 border-gray-300 text-primary focus:ring-primary" 
                    id="status-active" 
                    name="status" 
                    type="radio"
                    onChange={() => setFormData({...formData, status: 'active'})}
                  />
                  <label className="ml-2 block text-sm text-gray-900 dark:text-gray-100" htmlFor="status-active">Aktif</label>
                </div>
                <div className="flex items-center mt-2">
                  <input 
                    checked={formData.status === 'inactive'}
                    className="h-4 w-4 border-gray-300 text-primary focus:ring-primary" 
                    id="status-inactive" 
                    name="status" 
                    type="radio"
                    onChange={() => setFormData({...formData, status: 'inactive'})}
                  />
                  <label className="ml-2 block text-sm text-gray-900 dark:text-gray-100" htmlFor="status-inactive">Pasif</label>
                </div>
              </div>
            </div>
            <div className="flex justify-end gap-3 p-4 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-200 dark:border-gray-800 rounded-b-xl">
              <button className="rounded-lg h-10 px-4 text-sm font-bold bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700">
                İptal
              </button>
              <button 
                onClick={handleAddUser}
                className="rounded-lg h-10 px-4 text-sm font-bold bg-primary text-white hover:bg-primary/90"
              >
                Kaydet
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};