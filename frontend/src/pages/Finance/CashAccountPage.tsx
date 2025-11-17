import { useState } from 'react';
import { FinanceApi, CashTransaction, CashStats, CreateCashTransactionRequest } from '../../api/modules/finance';
import { useAsyncData } from '../../hooks/useAsyncData';

export const CashAccountPage = () => {
  const [filters, setFilters] = useState({
    startDate: '2024-01-01',
    endDate: '2024-12-31',
    type: 'all',
    caseSearch: '',
  });
  const [showCreate, setShowCreate] = useState(false);
  const [isSaving, setIsSaving] = useState(false);
  const [saveError, setSaveError] = useState<string | null>(null);
  const [form, setForm] = useState<CreateCashTransactionRequest>({
    type: 'income',
    amount: 0,
    occurredOn: new Date().toISOString().slice(0, 10),
    description: '',
  });

  const {
    data: stats,
    isLoading: statsLoading,
    refetch: refetchStats,
  } = useAsyncData(['cash-stats'], FinanceApi.getCashStats);

  const {
    data: transactions,
    isLoading: transactionsLoading,
    refetch: refetchTransactions,
  } = useAsyncData(['cash-transactions'], FinanceApi.getCashTransactions);

  if (statsLoading || transactionsLoading) return <p>Kasa bilgileri yükleniyor...</p>;

  const currentStats: CashStats = stats ?? {
    totalIncome: 0,
    totalExpense: 0,
    netBalance: 0,
  };

  const currentTransactions: CashTransaction[] = transactions ?? [];

  const handleCreate = async () => {
    try {
      setIsSaving(true);
      setSaveError(null);
      await FinanceApi.createCashTransaction(form);
      await Promise.all([refetchStats(), refetchTransactions()]);
      setShowCreate(false);
      setForm({
        type: 'income',
        amount: 0,
        occurredOn: new Date().toISOString().slice(0, 10),
        description: '',
      });
    } catch (error) {
      setSaveError('Kasa kaydı oluşturulurken bir hata oluştu.');
      // eslint-disable-next-line no-console
      console.error('Error creating cash transaction', error);
    } finally {
      setIsSaving(false);
    }
  };

  return (
    <div className="w-full max-w-7xl mx-auto">
      {/* PageHeading */}
      <header className="flex flex-wrap items-center justify-between gap-4 pb-6">
        <div className="flex flex-col gap-1">
          <h1 className="text-text-light dark:text-text-dark text-3xl font-bold leading-tight tracking-tight">Kasa</h1>
          <p className="text-text-secondary-light dark:text-text-secondary-dark text-base font-normal leading-normal">
            Tüm finansal gelir ve gider kayıtlarınızı yönetin.
          </p>
        </div>
        <button
          type="button"
          onClick={() => setShowCreate(true)}
          className="flex min-w-[140px] cursor-pointer items-center justify-center gap-2 overflow-hidden rounded-lg h-10 px-4 bg-emerald-600 text-white text-sm font-bold leading-normal tracking-wide shadow-sm hover:bg-emerald-700"
        >
          <span className="material-symbols-outlined text-base">add_circle</span>
          <span className="truncate">Yeni Kasa Kaydı</span>
        </button>
      </header>

      {showCreate && (
        <section className="mb-6">
          <div className="rounded-xl border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark p-6 space-y-4">
            <div className="flex items-center justify-between">
              <div>
                <h2 className="text-base font-semibold text-text-light dark:text-text-dark">Yeni Kasa Kaydı</h2>
                <p className="text-xs text-text-secondary-light dark:text-text-secondary-dark">
                  Gelir veya gider kaydı ekleyin.
                </p>
              </div>
              <button
                type="button"
                onClick={() => setShowCreate(false)}
                className="text-xs font-medium text-text-secondary-light dark:text-text-secondary-dark hover:text-text-light"
              >
                Kapat
              </button>
            </div>
            <div className="grid grid-cols-1 gap-4 md:grid-cols-4">
              <label className="flex flex-col gap-1 text-sm">
                <span className="text-text-light dark:text-text-dark">Tarih</span>
                <input
                  type="date"
                  className="form-input h-10 rounded-lg border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark px-3 text-sm text-text-light dark:text-text-dark"
                  value={form.occurredOn}
                  onChange={(e) => setForm({ ...form, occurredOn: e.target.value })}
                />
              </label>
              <label className="flex flex-col gap-1 text-sm">
                <span className="text-text-light dark:text-text-dark">Tür</span>
                <select
                  className="form-select h-10 rounded-lg border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark px-3 text-sm text-text-light dark:text-text-dark"
                  value={form.type}
                  onChange={(e) =>
                    setForm({ ...form, type: e.target.value === 'expense' ? 'expense' : 'income' })
                  }
                >
                  <option value="income">Gelir</option>
                  <option value="expense">Gider</option>
                </select>
              </label>
              <label className="flex flex-col gap-1 text-sm">
                <span className="text-text-light dark:text-text-dark">Tutar</span>
                <input
                  type="number"
                  min="0"
                  step="0.01"
                  className="form-input h-10 rounded-lg border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark px-3 text-sm text-text-light dark:text-text-dark"
                  value={form.amount}
                  onChange={(e) => setForm({ ...form, amount: Number(e.target.value) || 0 })}
                />
              </label>
              <label className="flex flex-col gap-1 text-sm md:col-span-1">
                <span className="text-text-light dark:text-text-dark">Açıklama</span>
                <input
                  type="text"
                  className="form-input h-10 rounded-lg border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark px-3 text-sm text-text-light dark:text-text-dark"
                  placeholder="Kısa açıklama..."
                  value={form.description ?? ''}
                  onChange={(e) => setForm({ ...form, description: e.target.value })}
                />
              </label>
            </div>
            {saveError && <p className="text-xs text-red-500">{saveError}</p>}
            <div className="flex justify-end gap-2">
              <button
                type="button"
                onClick={() => setShowCreate(false)}
                className="rounded-lg border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark px-4 py-2 text-xs font-semibold text-text-secondary-light dark:text-text-secondary-dark"
              >
                İptal
              </button>
              <button
                type="button"
                onClick={handleCreate}
                disabled={isSaving || form.amount <= 0}
                className={`rounded-lg px-4 py-2 text-xs font-semibold text-white ${
                  isSaving || form.amount <= 0 ? 'bg-gray-300 cursor-not-allowed' : 'bg-primary hover:bg-primary/90'
                }`}
              >
                {isSaving ? 'Kaydediliyor...' : 'Kaydı Oluştur'}
              </button>
            </div>
          </div>
        </section>
      )}

      {/* Stats */}
      <section className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 pb-8">
        <div className="flex flex-col gap-2 rounded-xl p-6 border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark">
          <div className="flex items-center gap-2">
            <span className="material-symbols-outlined text-success">trending_up</span>
            <p className="text-text-light dark:text-text-dark text-base font-medium leading-normal">Toplam Gelir</p>
          </div>
          <p className="text-text-light dark:text-text-dark tracking-tight text-3xl font-bold leading-tight">
            ₺{currentStats.totalIncome.toLocaleString('tr-TR', { minimumFractionDigits: 2 })}
          </p>
        </div>
        <div className="flex flex-col gap-2 rounded-xl p-6 border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark">
          <div className="flex items-center gap-2">
            <span className="material-symbols-outlined text-danger">trending_down</span>
            <p className="text-text-light dark:text-text-dark text-base font-medium leading-normal">Toplam Gider</p>
          </div>
          <p className="text-text-light dark:text-text-dark tracking-tight text-3xl font-bold leading-tight">
            ₺{currentStats.totalExpense.toLocaleString('tr-TR', { minimumFractionDigits: 2 })}
          </p>
        </div>
        <div className="flex flex-col gap-2 rounded-xl p-6 border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark">
          <div className="flex items-center gap-2">
            <span className="material-symbols-outlined text-primary">account_balance_wallet</span>
            <p className="text-text-light dark:text-text-dark text-base font-medium leading-normal">Net Durum</p>
          </div>
          <p className="text-text-light dark:text-text-dark tracking-tight text-3xl font-bold leading-tight">
            ₺{currentStats.netBalance.toLocaleString('tr-TR', { minimumFractionDigits: 2 })}
          </p>
        </div>
      </section>

      {/* Filter Card */}
      <section className="mb-8">
        <div className="rounded-xl border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark p-6">
          <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5">
            <label className="flex flex-col">
              <p className="text-text-light dark:text-text-dark text-sm font-medium leading-normal pb-2">
                Başlangıç Tarihi
              </p>
              <input
                className="form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-text-light dark:text-text-dark focus:outline-0 focus:ring-2 focus:ring-primary/50 border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark h-11 placeholder:text-text-secondary-light dark:placeholder:text-text-secondary-dark px-3 text-sm font-normal leading-normal"
                type="date"
                value={filters.startDate}
                onChange={(e) => setFilters({ ...filters, startDate: e.target.value })}
              />
            </label>
            <label className="flex flex-col">
              <p className="text-text-light dark:text-text-dark text-sm font-medium leading-normal pb-2">
                Bitiş Tarihi
              </p>
              <input
                className="form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-text-light dark:text-text-dark focus:outline-0 focus:ring-2 focus:ring-primary/50 border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark h-11 placeholder:text-text-secondary-light dark:placeholder:text-text-secondary-dark px-3 text-sm font-normal leading-normal"
                type="date"
                value={filters.endDate}
                onChange={(e) => setFilters({ ...filters, endDate: e.target.value })}
              />
            </label>
            <label className="flex flex-col">
              <p className="text-text-light dark:text-text-dark text-sm font-medium leading-normal pb-2">Tür</p>
              <select
                className="form-select flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-text-light dark:text-text-dark focus:outline-0 focus:ring-2 focus:ring-primary/50 border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark h-11 px-3 text-sm font-normal leading-normal"
                value={filters.type}
                onChange={(e) => setFilters({ ...filters, type: e.target.value })}
              >
                <option value="all">Tümü</option>
                <option value="income">Gelir</option>
                <option value="expense">Gider</option>
              </select>
            </label>
            <label className="flex flex-col">
              <p className="text-text-light dark:text-text-dark text-sm font-medium leading-normal pb-2">Dosya</p>
              <input
                className="form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-text-light dark:text-text-dark focus:outline-0 focus:ring-2 focus:ring-primary/50 border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark h-11 placeholder:text-text-secondary-light dark:placeholder:text-text-secondary-dark px-3 text-sm font-normal leading-normal"
                type="text"
                placeholder="Dosya adı veya no..."
                value={filters.caseSearch}
                onChange={(e) => setFilters({ ...filters, caseSearch: e.target.value })}
              />
            </label>
            <div className="flex items-end gap-2 pt-1 md:col-span-2 lg:col-span-4 xl:col-span-1">
              <button className="flex h-11 w-full cursor-pointer items-center justify-center overflow-hidden rounded-lg bg-primary px-4 text-sm font-bold text-white shadow-sm transition-colors hover:bg-primary/90">
                <span className="truncate">Filtrele</span>
              </button>
              <button className="flex h-11 w-full cursor-pointer items-center justify-center overflow-hidden rounded-lg bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark px-4 text-sm font-bold text-text-secondary-light dark:text-text-secondary-dark shadow-sm transition-colors hover:bg-black/5 dark:hover:bg-white/5">
                <span className="truncate">Temizle</span>
              </button>
            </div>
          </div>
        </div>
      </section>

      {/* Data Table */}
      <section>
        <div className="overflow-x-auto rounded-xl border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark">
          <table className="min-w-full divide-y divide-border-light dark:divide-border-dark">
            <thead className="bg-background-light dark:bg-background-dark">
              <tr>
                <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">
                  Tarih
                </th>
                <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">
                  Dosya
                </th>
                <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">
                  Müvekkil
                </th>
                <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">
                  Tür
                </th>
                <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">
                  Kategori
                </th>
                <th className="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">
                  Tutar
                </th>
              </tr>
            </thead>
            <tbody className="divide-y divide-border-light dark:divide-border-dark">
              {currentTransactions.length === 0 ? (
                <tr>
                  <td
                    colSpan={6}
                    className="px-6 py-6 text-center text-sm text-text-secondary-light dark:text-text-secondary-dark"
                  >
                    Henüz kasa hareketi bulunmuyor.
                  </td>
                </tr>
              ) : (
                currentTransactions.map((transaction) => (
                  <tr key={transaction.id}>
                    <td className="whitespace-nowrap px-6 py-4 text-sm text-text-light dark:text-text-dark">
                      {transaction.date}
                    </td>
                    <td className="whitespace-nowrap px-6 py-4 text-sm text-text-light dark:text-text-dark">
                      {transaction.caseNumber ?? '-'}
                    </td>
                    <td className="whitespace-nowrap px-6 py-4 text-sm text-text-light dark:text-text-dark">
                      {transaction.clientName ?? '-'}
                    </td>
                    <td className="whitespace-nowrap px-6 py-4 text-sm">
                      <span
                        className={`inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ${
                          transaction.type === 'income'
                            ? 'bg-emerald-50 text-emerald-700'
                            : 'bg-rose-50 text-rose-700'
                        }`}
                      >
                        {transaction.type === 'income' ? 'Gelir' : 'Gider'}
                      </span>
                    </td>
                    <td className="whitespace-nowrap px-6 py-4 text-sm text-text-light dark:text-text-dark">
                      {transaction.category}
                    </td>
                    <td className="whitespace-nowrap px-6 py-4 font-semibold text-right">
                      ₺{transaction.amount.toLocaleString('tr-TR', { minimumFractionDigits: 2 })}
                    </td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>
      </section>
    </div>
  );
};


