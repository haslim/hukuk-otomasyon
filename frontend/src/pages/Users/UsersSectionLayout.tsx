import { ReactNode } from 'react';
import { Link } from 'react-router-dom';

const sections = [
  {
    key: 'users',
    label: 'Kullanıcılar',
    description: 'Kullanıcı listesi ve yönetimi',
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
      <header className="rounded-2xl border border-gray-200 bg-white px-6 py-5 shadow-sm flex flex-col gap-1">
        <p className="text-xs font-semibold uppercase tracking-[0.25em] text-gray-500">
          Kullanıcı Yönetimi
        </p>
        <h1 className="text-2xl font-bold text-gray-900">Kullanıcılar & Roller</h1>
        <p className="text-sm text-gray-500">
          Kullanıcı hesapları, roller ve yetki setleri arasında hızlıca geçiş yapın.
        </p>
        <div className="mt-4 inline-flex rounded-full bg-gray-100 p-1 text-sm font-medium">
          {sections.map((section) => {
            const isActive = activeTab === section.key;
            return (
              <Link
                key={section.key}
                to={section.to}
                className={`px-4 py-1.5 rounded-full transition-colors ${
                  isActive
                    ? 'bg-primary text-white shadow-sm'
                    : 'text-gray-600 hover:text-primary'
                }`}
                aria-current={isActive ? 'page' : undefined}
              >
                {section.label}
              </Link>
            );
          })}
        </div>
      </header>
      <main className="space-y-6">{children}</main>
    </div>
  );
};

