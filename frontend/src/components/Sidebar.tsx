import { NavLink, useNavigate } from 'react-router-dom';
import { apiClient } from '../api/client';
import { useAuth } from '../context/AuthContext';

const links = [
  { to: '/', label: 'Dashboard', icon: 'dashboard' },
  { to: '/cases', label: 'Dosyalar', icon: 'folder' },
  { to: '/mediation', label: 'Arabuluculuk', icon: 'handshake' },
  { to: '/clients', label: 'Müvekkiller', icon: 'group' },
  { to: '/finance/cash', label: 'Kasa', icon: 'account_balance_wallet' },
  { to: '/calendar', label: 'Takvim', icon: 'calendar_month' },
  { to: '/users', label: 'Kullanıcılar', icon: 'manage_accounts' },
  { to: '/users/roles', label: 'Roller & Yetkiler', icon: 'admin_panel_settings' },
  { to: '/documents', label: 'Dokümanlar', icon: 'folder_open' },
  { to: '/notifications', label: 'Bildirimler', icon: 'notifications' },
  { to: '/workflow', label: 'Workflow', icon: 'route' },
  { to: '/search', label: 'Arama', icon: 'search' },
];

export const Sidebar = () => {
  const navigate = useNavigate();
  const { setToken } = useAuth();

  const handleLogout = async () => {
    try {
      await apiClient.post('/auth/logout');
    } catch {
      // ignore API logout errors
    }
    setToken(null);
    navigate('/');
  };

  return (
    <aside className="flex h-screen w-64 flex-col bg-[#2D3748] text-[#E2E8F0] p-4 sticky top-0">
      <div className="flex items-center gap-3 px-3 py-4 mb-4">
        <div className="size-8 text-[#2463eb]">
          <svg fill="none" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
            <path d="M44 4H30.6666V17.3334H17.3334V30.6666H4V44H44V4Z" fill="currentColor" />
          </svg>
        </div>
        <h1 className="text-white text-xl font-bold">BGAofis</h1>
      </div>
      <nav className="flex flex-col gap-2">
        {links.map((link) => (
          <NavLink
            key={link.to}
            to={link.to}
            className={({ isActive }) =>
              `relative flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors duration-200 ${
                isActive ? 'bg-[#2463eb]/20 text-white' : 'hover:bg-[#2463eb]/20 text-[#A0AEC0]'
              }`
            }
            end={link.to === '/'}
          >
            {({ isActive }) => (
              <>
                {isActive && <span className="absolute left-0 top-0 bottom-0 w-1 bg-[#2463eb] rounded-r-full" />}
                <span className={`material-symbols-outlined ${isActive ? 'text-white' : 'text-[#A0AEC0]'}`}>
                  {link.icon}
                </span>
                <span className={isActive ? 'font-semibold text-white' : ''}>{link.label}</span>
              </>
            )}
          </NavLink>
        ))}
      </nav>
      <button
        type="button"
        onClick={handleLogout}
        className="mt-auto flex items-center gap-3 p-3 rounded-lg bg-black/20 hover:bg-black/30 transition-colors"
      >
        <div
          className="bg-center bg-no-repeat aspect-square bg-cover rounded-full size-10"
          style={{
            backgroundImage:
              'url("https://images.unsplash.com/photo-1524504388940-b1c1722653e1?w=200&auto=format&fit=crop&q=80")',
          }}
        />
        <div className="flex flex-col">
          <h1 className="text-white text-sm font-semibold leading-tight">Can Yılmaz</h1>
          <p className="text-[#A0AEC0] text-xs leading-tight">Avukat</p>
        </div>
        <span className="material-symbols-outlined ml-auto text-white">logout</span>
      </button>
    </aside>
  );
};

