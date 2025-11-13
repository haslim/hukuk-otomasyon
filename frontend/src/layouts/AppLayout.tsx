import { ReactNode } from 'react';
import { Sidebar } from '../components/Sidebar';

interface Props {
  children: ReactNode;
}

export const AppLayout = ({ children }: Props) => (
  <div className="flex min-h-screen bg-slate-100">
    <Sidebar />
    <main className="flex-1 p-6 space-y-6">{children}</main>
  </div>
);
