import { FinanceApi } from '../../api/modules/finance';
import { useAsyncData } from '../../hooks/useAsyncData';
import { Link } from 'react-router-dom';

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

      <div className="mt-6">
        <Link
          to="/finance/cash"
          className="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-white font-medium hover:bg-primary/90 transition-colors"
        >
          <span className="material-symbols-outlined">account_balance_wallet</span>
          Kasa Hesabı Detayları
        </Link>
      </div>
    </section>
  );
};
