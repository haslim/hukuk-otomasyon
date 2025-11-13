import { FinanceApi } from '../../api/modules/finance';
import { useAsyncData } from '../../hooks/useAsyncData';

export const FinancePage = () => {
  const { data, isLoading } = useAsyncData(['cash-flow'], FinanceApi.cashFlow);

  if (isLoading) return <p>Kasa bilgileri yükleniyor...</p>;

  return (
    <section className="space-y-4">
      <header>
        <h2 className="text-2xl font-semibold">Kasa & Finans</h2>
        <p className="text-slate-600">Tahsilat, masraf ve aylık rapor görünümü</p>
      </header>

      <div className="grid grid-cols-2 gap-4">
        <article className="rounded bg-white p-4 shadow">
          <h3 className="text-sm text-slate-500">Gelirler</h3>
          <p className="text-3xl font-bold text-emerald-600">₺{data?.income ?? 0}</p>
        </article>
        <article className="rounded bg-white p-4 shadow">
          <h3 className="text-sm text-slate-500">Giderler</h3>
          <p className="text-3xl font-bold text-rose-600">₺{data?.expense ?? 0}</p>
        </article>
      </div>
    </section>
  );
};
