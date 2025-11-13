import { DashboardApi } from '../../api/modules/dashboard';
import { useAsyncData } from '../../hooks/useAsyncData';

export const DashboardPage = () => {
  const { data, isLoading } = useAsyncData(['dashboard'], DashboardApi.overview);

  if (isLoading) return <p>Yükleniyor...</p>;

  return (
    <section className="space-y-4">
      <h2 className="text-2xl font-semibold">Genel Bakış</h2>
      <div className="grid grid-cols-2 gap-4">
        <article className="rounded-lg bg-white p-4 shadow">
          <h3 className="text-sm text-slate-500">Bugünkü Duruşmalar</h3>
          <p className="text-3xl font-bold">{data?.hearings_today ?? 0}</p>
        </article>
        <article className="rounded-lg bg-white p-4 shadow">
          <h3 className="text-sm text-slate-500">Yakın Sona Erecek Görevler</h3>
          <p className="text-3xl font-bold">{data?.upcoming_deadlines ?? 0}</p>
        </article>
        <article className="rounded-lg bg-white p-4 shadow">
          <h3 className="text-sm text-slate-500">Açık Görevler</h3>
          <p className="text-3xl font-bold">{data?.open_tasks ?? 0}</p>
        </article>
        <article className="rounded-lg bg-white p-4 shadow">
          <h3 className="text-sm text-slate-500">Kasa Durumu</h3>
          <p className="text-lg">Gelir: ₺{data?.cash_summary?.income ?? 0}</p>
          <p className="text-lg">Gider: ₺{data?.cash_summary?.expense ?? 0}</p>
        </article>
      </div>
    </section>
  );
};
