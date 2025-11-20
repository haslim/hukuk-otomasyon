import { ReactNode, useState } from 'react';
import { useLocation } from 'react-router-dom';
import { Sidebar } from '../components/Sidebar';
import { DEFAULT_AVATAR_URL, useAuth } from '../context/AuthContext';
import { ProfileModal } from '../components/ProfileModal';
import { NotificationDropdown } from '../components/NotificationDropdown';

const titles: Record<string, string> = {
  '/': 'Dashboard',
  '/clients': 'Müvekkiller',
  '/cases': 'Dosyalar',
  '/workflow': 'Workflow',
  '/documents': 'Dokümanlar',
  '/finance': 'Kasa',
  '/notifications': 'Bildirimler',
  '/search': 'Gelişmiş Arama',
  '/users': 'Kullanıcı Yönetimi',
  '/users/roles': 'Roller & Yetkiler',
  '/mediation': 'Arabuluculuk',
  '/mediation/list': 'Arabuluculuk Dosyaları',
  '/mediation/new': 'Yeni Arabuluculuk Başvurusu',
  '/arbitration': 'Arabuluculuk Başvuruları',
  '/arbitration/dashboard': 'Arabuluculuk İstatistikleri',
  '/profile': 'Profilim',
};

interface Props {
  children: ReactNode;
}

export const AppLayout = ({ children }: Props) => {
  const location = useLocation();
  const { user, logout } = useAuth();
  const [profileOpen, setProfileOpen] = useState(false);
  const [profileModalOpen, setProfileModalOpen] = useState(false);
  const [mobileSidebarOpen, setMobileSidebarOpen] = useState(false);

  const dynamicTitle = location.pathname.startsWith('/cases/')
    ? 'Dosya Detayı'
    : location.pathname.startsWith('/mediation/') &&
      location.pathname !== '/mediation' &&
      location.pathname !== '/mediation/new'
      ? 'Arabuluculuk Detayı'
      : undefined;
  const pageTitle = dynamicTitle ?? titles[location.pathname] ?? 'BGAofis';

  return (
    <div className="relative flex min-h-screen w-full bg-[#F6F6F8] text-[#1A202C]">
      <div className="hidden lg:flex lg:sticky lg:top-0 lg:h-screen lg:w-64">
        <Sidebar className="will-change-transform transform-gpu backdrop-filter-none" />
      </div>
      <main className="flex-1 flex flex-col">
        <header className="flex flex-col gap-4 border-b border-[#E2E8F0] bg-white px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-8">
          <div>
            <h2 className="text-xl font-bold leading-tight">{pageTitle}</h2>
            <p className="text-sm text-[#A0AEC0]">BGOfis yönetim paneli</p>
          </div>
          <div className="flex w-full flex-1 flex-wrap items-center justify-end gap-3">
            <button
              type="button"
              className="lg:hidden rounded-lg border border-[#E2E8F0] p-2 text-[#1A202C] hover:bg-gray-100 transition-colors"
              onClick={() => setMobileSidebarOpen(true)}
            >
              <span className="material-symbols-outlined text-xl">menu</span>
            </button>
            <label className="relative flex flex-1 max-w-xs min-w-[220px]">
              <div className="absolute left-3 top-1/2 -translate-y-1/2 text-[#A0AEC0]">
                <span className="material-symbols-outlined text-xl">search</span>
              </div>
              <input
                className="h-10 w-full rounded-lg border border-[#E2E8F0] bg-white px-4 pl-10 text-sm text-[#1A202C] focus:outline-none focus:ring-2 focus:ring-[#2463eb]"
                placeholder="Ara..."
              />
            </label>
            <NotificationDropdown />
            <div className="relative">
              <button
                type="button"
                onClick={() => setProfileOpen((prev) => !prev)}
                className="flex items-center gap-3 rounded-lg px-2 py-1 hover:bg-gray-100"
              >
                <div className="bg-center bg-no-repeat aspect-square bg-cover rounded-full size-10 overflow-hidden">
                  <img
                    src={user?.avatarUrl || DEFAULT_AVATAR_URL}
                    alt={user?.name ?? 'Profil resmi'}
                    className="h-full w-full object-cover"
                  />
                </div>
                <div className="flex flex-col text-right">
                  <span className="text-sm font-semibold">{user?.name ?? 'Ali Haydar Aslim'}</span>
                  <span className="text-xs text-[#A0AEC0]">{user?.title ?? 'Avukat'}</span>
                </div>
                <span className="material-symbols-outlined text-[#A0AEC0]">expand_more</span>
              </button>
              {profileOpen && (
                <div className="absolute right-0 mt-2 w-44 rounded-lg border border-[#E2E8F0] bg-white shadow-md z-20">
                  <button
                    type="button"
                    onClick={() => {
                      setProfileModalOpen(true);
                      setProfileOpen(false);
                    }}
                    className="flex w-full items-center gap-2 px-4 py-2 text-sm text-[#1A202C] hover:bg-gray-50"
                  >
                    <span className="material-symbols-outlined text-base">manage_accounts</span>
                    Profili Düzenle
                  </button>
                  <button
                    type="button"
                    onClick={logout}
                    className="flex w-full items-center gap-2 px-4 py-2 text-sm text-[#1A202C] hover:bg-gray-50"
                  >
                    <span className="material-symbols-outlined text-base">logout</span>
                    Çıkış Yap
                  </button>
                </div>
              )}
            </div>
          </div>
        </header>
        <div className="flex-1 p-4 sm:p-8 bg-[#F7FAFC]">{children}</div>
      </main>
      {mobileSidebarOpen && (
        <div className="fixed inset-0 z-40 lg:hidden">
          <div
            className="absolute inset-0 bg-black/40"
            onClick={() => setMobileSidebarOpen(false)}
          />
          <div className="relative z-10 h-full w-64 bg-transparent">
            <Sidebar className="h-full sticky top-0" onLinkClick={() => setMobileSidebarOpen(false)} />
          </div>
        </div>
      )}
      <ProfileModal open={profileModalOpen} onClose={() => setProfileModalOpen(false)} />
    </div>
  );
};
