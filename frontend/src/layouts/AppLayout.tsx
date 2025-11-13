import { ReactNode } from 'react';
import { useLocation } from 'react-router-dom';
import { Sidebar } from '../components/Sidebar';

const titles: Record<string, string> = {
  '/': 'Dashboard',
  '/clients': 'Müvekkiller',
  '/cases': 'Dosyalar',
  '/workflow': 'Workflow',
  '/documents': 'Dokümanlar',
  '/finance': 'Kasa',
  '/notifications': 'Bildirimler',
  '/search': 'Gelişmiş Arama',
};

interface Props {
  children: ReactNode;
}

export const AppLayout = ({ children }: Props) => {
  const location = useLocation();
  const dynamicTitle = location.pathname.startsWith('/cases/') ? 'Dosya Detayı' : undefined;
  const pageTitle = dynamicTitle ?? titles[location.pathname] ?? 'BGAofis';

  return (
    <div className="relative flex min-h-screen w-full bg-[#F6F6F8] text-[#1A202C]">
      <Sidebar />
      <main className="flex-1 flex flex-col">
        <header className="flex items-center justify-between border-b border-[#E2E8F0] bg-white px-8 py-4 sticky top-0 z-10">
          <div>
            <h2 className="text-xl font-bold leading-tight">{pageTitle}</h2>
            <p className="text-sm text-[#A0AEC0]">BGOfis yönetim paneli</p>
          </div>
          <div className="flex flex-1 justify-end items-center gap-4">
            <label className="relative flex flex-col w-full max-w-xs">
              <div className="absolute left-3 top-1/2 -translate-y-1/2 text-[#A0AEC0]">
                <span className="material-symbols-outlined text-xl">search</span>
              </div>
              <input
                className="h-10 rounded-lg border border-[#E2E8F0] bg-white px-4 pl-10 text-sm text-[#1A202C] focus:outline-none focus:ring-2 focus:ring-[#2463eb]"
                placeholder="Ara..."
              />
            </label>
            <button className="relative flex items-center justify-center rounded-lg h-10 w-10 border border-[#E2E8F0] hover:bg-gray-100 transition-colors">
              <span className="material-symbols-outlined text-[#1A202C]">notifications</span>
              <span className="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-white text-[10px] font-bold">
                3
              </span>
            </button>
            <div className="flex items-center gap-3">
              <div
                className="bg-center bg-no-repeat aspect-square bg-cover rounded-full size-10"
                style={{
                  backgroundImage:
                    'url("https://images.unsplash.com/photo-1524504388940-b1c1722653e1?w=200&auto=format&fit=crop&q=80")',
                }}
              />
              <div className="flex flex-col text-right">
                <span className="text-sm font-semibold">Can Yılmaz</span>
                <span className="text-xs text-[#A0AEC0]">Avukat</span>
              </div>
              <button className="text-[#A0AEC0]">
                <span className="material-symbols-outlined">expand_more</span>
              </button>
            </div>
          </div>
        </header>
        <div className="flex-1 p-8 bg-[#F7FAFC]">{children}</div>
      </main>
    </div>
  );
};
