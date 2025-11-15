import { ReactNode } from 'react';
import { Link } from 'react-router-dom';

const sections = [
  {
    key: 'users',
    label: 'KullanÄ±cÄ±lar',
    description: 'KullanÄ±cÄ± listesi & yÃ¶netimi',
    to: '/users',
  },
  {
    key: 'roles',
    label: 'Roller & Yetkiler',
    description: 'Roller ve yetki setleri',
    to: '/users/roles',
  },
];

interface Props {
  activeTab: 'users' | 'roles';
  children: ReactNode;
}

export const UsersSectionLayout = ({ activeTab, children }: Props) => {
  return (
    <div className="w-full max-w-7xl mx-auto space-y-6">
      <div className="grid gap-6 lg:grid-cols-[220px,1fr]">
        <nav className="rounded-2xl border border-gray-200 bg-white shadow-sm">
          <div className="px-5 py-4 border-b border-gray-200">
            <p className="text-xs font-semibold uppercase tracking-[0.3em] text-gray-500">
              YÃ¶netim
            </p>
            <h2 className="text-lg font-semibold text-gray-900 mt-1">KullanÄ±cÄ± BÃ¶lÃ¼mleri</h2>
            <p className="text-sm text-gray-500 mt-1">
              Ekranlar arasÄ±nda hÄ±zlÄ±ca geÃ§iÅŸ yapÄ±n.
            </p>
          </div>
          <div className="flex flex-col gap-2 p-2">
            {sections.map((section) => {
              const isActive = activeTab === section.key;
              return (
                <Link
                  key={section.key}
                  to={section.to}
                  className={`flex flex-col gap-0.5 rounded-xl px-4 py-3 text-sm font-medium transition-all ${
                    isActive
                      ? 'bg-primary/10 text-primary'
                      : 'text-gray-600 hover:bg-gray-50 hover:text-primary'
                  }`}
                  aria-current={isActive ? 'page' : undefined}
                >
                  <span>{section.label}</span>
                  <span className="text-xs font-normal text-gray-400">{section.description}</span>
                </Link>
              );
            })}
          </div>
        </nav>
        <div className="space-y-6">{children}</div>
      </div>
    </div>
  );
};

