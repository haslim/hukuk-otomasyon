import { NavLink } from 'react-router-dom';

const links = [
  { to: '/', label: 'Dashboard' },
  { to: '/clients', label: 'Müvekkiller' },
  { to: '/cases', label: 'Dosyalar' },
  { to: '/workflow', label: 'Workflow' },
  { to: '/documents', label: 'Dokümanlar' },
  { to: '/finance', label: 'Kasa' },
  { to: '/notifications', label: 'Bildirimler' },
  { to: '/search', label: 'Arama' },
];

export const Sidebar = () => (
  <aside className="w-64 bg-slate-900 text-white p-4 space-y-4">
    <h1 className="text-xl font-bold">BGAofis</h1>
    <nav className="space-y-2">
      {links.map((link) => (
        <NavLink
          key={link.to}
          to={link.to}
          className={({ isActive }) =>
            `block rounded px-3 py-2 text-sm ${isActive ? 'bg-white/20' : 'hover:bg-white/10'}`
          }
          end={link.to === '/'}
        >
          {link.label}
        </NavLink>
      ))}
    </nav>
  </aside>
);
