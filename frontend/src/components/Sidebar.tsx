import { NavLink, useNavigate } from 'react-router-dom';
import { apiClient } from '../api/client';
import { useAuth } from '../context/AuthContext';

const links = [
  { to: '/', label: 'Dashboard', icon: 'dashboard' },
  { to: '/profile', label: 'Profilim', icon: 'account_circle' },
  { to: '/cases', label: 'Dosyalar', icon: 'folder' },
  { to: '/mediation', label: 'Arabuluculuk', icon: 'handshake' },
  { to: '/clients', label: 'Müvekkiller', icon: 'group' },
  { to: '/finance/cash', label: 'Kasa', icon: 'account_balance_wallet' },
  { to: '/calendar', label: 'Takvim', icon: 'calendar_month' },
  { to: '/users', label: 'Kullanıcılar & Roller', icon: 'manage_accounts' },
  { to: '/documents', label: 'Dokümanlar', icon: 'folder_open' },
  { to: '/notifications', label: 'Bildirimler', icon: 'notifications' },
  { to: '/workflow', label: 'Workflow', icon: 'route' },
  { to: '/search', label: 'Arama', icon: 'search' },
];

interface SidebarProps {
  className?: string;
  onLinkClick?: () => void;
}

export const Sidebar = ({ className = '', onLinkClick }: SidebarProps) => {
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
    <aside
      className={`flex h-full w-64 flex-col bg-[#1A2234] text-[#E2E8F0] p-4 ${className}`}
    >
      <div className="flex items-center gap-3 px-3 py-4 mb-6 border-b border-white/5">
        <div className="size-9 rounded-xl bg-[#2463eb]/10 flex items-center justify-center text-[#2463eb]">
          <svg fill="none" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg" className="w-6 h-6">
            <path d="M44 4H30.6666V17.3334H17.3334V30.6666H4V44H44V4Z" fill="currentColor" />
          </svg>
        </div>
        <div className="flex flex-col">
          <h1 className="text-white text-xl font-bold leading-tight">BGAofis</h1>
          <p className="text-[11px] uppercase tracking-[0.2em] text-[#A0AEC0]">Hukuk Otomasyon</p>
        </div>
      </div>
      <nav className="flex flex-col gap-1">
        {links.map((link) => (
          <NavLink
            key={link.to}
            to={link.to}
            className={({ isActive }) =>
              `relative flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors duration-200 ${
                isActive
                  ? link.to === '/users'
                    ? 'bg-emerald-600/20 text-emerald-100'
                    : 'bg-[#2463eb]/15 text-white'
                  : 'hover:bg-white/5 text-[#A0AEC0]'
              }`
            }
            end={link.to === '/'}
            onClick={onLinkClick}
          >
            {({ isActive }) => (
              <>
                {isActive && (
                  <span
                    className={`absolute left-0 top-1 bottom-1 w-1 rounded-r-full ${
                      link.to === '/users' ? 'bg-emerald-500' : 'bg-[#2463eb]'
                    }`}
                  />
                )}
                <span
                  className={`material-symbols-outlined text-base ${
                    isActive
                      ? link.to === '/users'
                        ? 'text-emerald-400'
                        : 'text-white'
                      : 'text-[#718096]'
                  }`}
                >
                  {link.icon}
                </span>
                <span
                  className={
                    isActive
                      ? link.to === '/users'
                        ? 'font-semibold text-emerald-100'
                        : 'font-semibold text-white'
                      : 'text-sm'
                  }
                >
                  {link.label}
                </span>
              </>
            )}
          </NavLink>
        ))}
      </nav>
      <button
        type="button"
        onClick={handleLogout}
        className="mt-auto flex items-center gap-3 p-3 rounded-lg bg-white/5 hover:bg-white/10 transition-colors justify-center text-sm font-semibold text-white"
      >
        <span className="material-symbols-outlined text-white text-base">logout</span>
        <span>Çıkış Yap</span>
      </button>
    </aside>
  );
};
