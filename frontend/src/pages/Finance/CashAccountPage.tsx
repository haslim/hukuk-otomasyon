import { useState } from 'react';
import { FinanceApi, CashTransaction, CashStats } from '../../api/modules/finance';
import { useAsyncData } from '../../hooks/useAsyncData';

export const CashAccountPage = () => {
  const [filters, setFilters] = useState({
    startDate: '2024-01-01',
    endDate: '2024-12-31',
    type: 'all',
    caseSearch: ''
  });

  const { data: stats, isLoading: statsLoading } = useAsyncData(['cash-stats'], FinanceApi.getCashStats);
  const { data: transactions, isLoading: transactionsLoading } = useAsyncData(['cash-transactions'], FinanceApi.getCashTransactions);

  if (statsLoading || transactionsLoading) return <p>Kasa bilgileri yükleniyor...</p>;

  const mockStats: CashStats = {
    totalIncome: 150000,
    totalExpense: 45000,
    netBalance: 105000
  };

  const mockTransactions: CashTransaction[] = [
    {
      id: '1',
      date: '15.05.2024',
      caseNumber: '2024/101 E.',
      clientName: 'Ahmet Yılmaz',
      type: 'income',
      category: 'Vekalet Ücreti',
      amount: 25000,
      description: 'Dava masrafları avansı'
    },
    {
      id: '2',
      date: '14.05.2024',
      caseNumber: '2023/245 E.',
      clientName: 'Ayşe Kara',
      type: 'expense',
      category: 'Mahkeme Harcı',
      amount: 1250,
      description: 'Temyiz başvuru harcı'
    },
    {
      id: '3',
      date: '12.05.2024',
      caseNumber: '-',
      clientName: '-',
      type: 'expense',
      category: 'Ofis Gideri',
      amount: 350,
      description: 'Kırtasiye malzemeleri'
    },
    {
      id: '4',
      date: '10.05.2024',
      caseNumber: '2024/50 E.',
      clientName: 'Mehmet Çelik',
      type: 'income',
      category: 'Danışmanlık',
      amount: 5000,
      description: 'Sözleşme danışmanlığı'
    },
    {
      id: '5',
      date: '08.05.2024',
      caseNumber: '2023/245 E.',
      clientName: 'Ayşe Kara',
      type: 'expense',
      category: 'Yol Masrafı',
      amount: 450,
      description: 'Ankara duruşması için yolculuk'
    }
  ];

  const currentStats = stats || mockStats;
  const currentTransactions = transactions || mockTransactions;

  return (
    <div className="w-full max-w-7xl mx-auto">
      {/* PageHeading */}
      <header className="flex flex-wrap items-center justify-between gap-4 pb-8">
        <div className="flex flex-col gap-1">
          <h1 className="text-text-light dark:text-text-dark text-3xl font-bold leading-tight tracking-tight">Kasa</h1>
          <p className="text-text-secondary-light dark:text-text-secondary-dark text-base font-normal leading-normal">Tüm finansal gelir ve gider kayıtlarınızı yönetin.</p>
        </div>
        <button className="flex min-w-[84px] cursor-pointer items-center justify-center gap-2 overflow-hidden rounded-lg h-10 px-4 bg-primary text-white text-sm font-bold leading-normal tracking-wide shadow-sm hover:bg-primary/90 transition-colors">
          <span className="material-symbols-outlined text-base">add_circle</span>
          <span className="truncate">Yeni Kasa Kaydı</span>
        </button>
      </header>

      {/* Stats */}
      <section className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 pb-8">
        <div className="flex flex-col gap-2 rounded-xl p-6 border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark">
          <div className="flex items-center gap-2">
            <span className="material-symbols-outlined text-success">trending_up</span>
            <p className="text-text-light dark:text-text-dark text-base font-medium leading-normal">Toplam Gelir</p>
          </div>
          <p className="text-text-light dark:text-text-dark tracking-tight text-3xl font-bold leading-tight">₺{currentStats.totalIncome.toLocaleString('tr-TR', { minimumFractionDigits: 2 })}</p>
        </div>
        <div className="flex flex-col gap-2 rounded-xl p-6 border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark">
          <div className="flex items-center gap-2">
            <span className="material-symbols-outlined text-danger">trending_down</span>
            <p className="text-text-light dark:text-text-dark text-base font-medium leading-normal">Toplam Gider</p>
          </div>
          <p className="text-text-light dark:text-text-dark tracking-tight text-3xl font-bold leading-tight">₺{currentStats.totalExpense.toLocaleString('tr-TR', { minimumFractionDigits: 2 })}</p>
        </div>
        <div className="flex flex-col gap-2 rounded-xl p-6 border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark">
          <div className="flex items-center gap-2">
            <span className="material-symbols-outlined text-primary">account_balance_wallet</span>
            <p className="text-text-light dark:text-text-dark text-base font-medium leading-normal">Net Durum</p>
          </div>
          <p className="text-text-light dark:text-text-dark tracking-tight text-3xl font-bold leading-tight">₺{currentStats.netBalance.toLocaleString('tr-TR', { minimumFractionDigits: 2 })}</p>
        </div>
      </section>

      {/* Filter Card */}
      <section className="mb-8">
        <div className="rounded-xl border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark p-6">
          <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5">
            <label className="flex flex-col">
              <p className="text-text-light dark:text-text-dark text-sm font-medium leading-normal pb-2">Başlangıç Tarihi</p>
              <input 
                className="form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-text-light dark:text-text-dark focus:outline-0 focus:ring-2 focus:ring-primary/50 border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark h-11 placeholder:text-text-secondary-light dark:placeholder:text-text-secondary-dark px-3 text-sm font-normal leading-normal" 
                type="date" 
                value={filters.startDate}
                onChange={(e) => setFilters({...filters, startDate: e.target.value})}
              />
            </label>
            <label className="flex flex-col">
              <p className="text-text-light dark:text-text-dark text-sm font-medium leading-normal pb-2">Bitiş Tarihi</p>
              <input 
                className="form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-text-light dark:text-text-dark focus:outline-0 focus:ring-2 focus:ring-primary/50 border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark h-11 placeholder:text-text-secondary-light dark:placeholder:text-text-secondary-dark px-3 text-sm font-normal leading-normal" 
                type="date" 
                value={filters.endDate}
                onChange={(e) => setFilters({...filters, endDate: e.target.value})}
              />
            </label>
            <label className="flex flex-col">
              <p className="text-text-light dark:text-text-dark text-sm font-medium leading-normal pb-2">Tür</p>
              <select 
                className="form-select flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-text-light dark:text-text-dark focus:outline-0 focus:ring-2 focus:ring-primary/50 border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark h-11 px-3 text-sm font-normal leading-normal"
                value={filters.type}
                onChange={(e) => setFilters({...filters, type: e.target.value})}
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
                onChange={(e) => setFilters({...filters, caseSearch: e.target.value})}
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
          <table className="min-w-full text-left text-sm">
            <thead className="border-b border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark/50">
              <tr>
                <th className="px-6 py-4 font-semibold text-text-light dark:text-text-dark" scope="col">Tarih</th>
                <th className="px-6 py-4 font-semibold text-text-light dark:text-text-dark" scope="col">Dosya</th>
                <th className="px-6 py-4 font-semibold text-text-light dark:text-text-dark" scope="col">Müvekkil</th>
                <th className="px-6 py-4 font-semibold text-text-light dark:text-text-dark" scope="col">Tür</th>
                <th className="px-6 py-4 font-semibold text-text-light dark:text-text-dark" scope="col">Kategori</th>
                <th className="px-6 py-4 font-semibold text-text-light dark:text-text-dark text-right" scope="col">Tutar</th>
                <th className="px-6 py-4 font-semibold text-text-light dark:text-text-dark" scope="col">Açıklama</th>
              </tr>
            </thead>
            <tbody>
              {currentTransactions.map((transaction: CashTransaction, index: number) => (
                <tr key={transaction.id} className={`border-b border-border-light dark:border-border-dark transition-colors hover:bg-black/5 dark:hover:bg-white/5 ${index === currentTransactions.length - 1 ? 'border-b-0' : ''}`}>
                  <td className="whitespace-nowrap px-6 py-4">{transaction.date}</td>
                  <td className="whitespace-nowrap px-6 py-4 font-medium">{transaction.caseNumber}</td>
                  <td className="whitespace-nowrap px-6 py-4">{transaction.clientName}</td>
                  <td className="whitespace-nowrap px-6 py-4">
                    <span className={`inline-flex items-center rounded-full px-2 py-1 text-xs font-medium ${
                      transaction.type === 'income' 
                        ? 'bg-success/10 text-success' 
                        : 'bg-danger/10 text-danger'
                    }`}>
                      {transaction.type === 'income' ? 'Gelir' : 'Gider'}
                    </span>
                  </td>
                  <td className="whitespace-nowrap px-6 py-4">{transaction.category}</td>
                  <td className="whitespace-nowrap px-6 py-4 font-semibold text-right">₺{transaction.amount.toLocaleString('tr-TR', { minimumFractionDigits: 2 })}</td>
                  <td className="px-6 py-4 max-w-xs truncate">{transaction.description}</td>
                </tr>
              ))}
            </tbody>
          </table>
          
          {/* Pagination */}
          <div className="flex flex-wrap items-center justify-between gap-4 p-4">
            <p className="text-sm text-text-secondary-light dark:text-text-secondary-dark">Toplam 125 kayıttan 1-5 arası gösteriliyor</p>
            <div className="flex items-center gap-2">
              <button className="flex h-8 w-8 items-center justify-center rounded-lg border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark text-text-secondary-light dark:text-text-secondary-dark transition-colors hover:bg-black/5 dark:hover:bg-white/5">
                <span className="material-symbols-outlined text-base">chevron_left</span>
              </button>
              <button className="flex h-8 w-8 items-center justify-center rounded-lg border border-primary bg-primary text-sm font-semibold text-white">1</button>
              <button className="flex h-8 w-8 items-center justify-center rounded-lg border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark text-sm text-text-secondary-light dark:text-text-secondary-dark transition-colors hover:bg-black/5 dark:hover:bg-white/5">2</button>
              <button className="flex h-8 w-8 items-center justify-center rounded-lg border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark text-sm text-text-secondary-light dark:text-text-secondary-dark transition-colors hover:bg-black/5 dark:hover:bg-white/5">3</button>
              <span className="text-text-secondary-light dark:text-text-secondary-dark">...</span>
              <button className="flex h-8 w-8 items-center justify-center rounded-lg border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark text-sm text-text-secondary-light dark:text-text-secondary-dark transition-colors hover:bg-black/5 dark:hover:bg-white/5">25</button>
              <button className="flex h-8 w-8 items-center justify-center rounded-lg border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark text-text-secondary-light dark:text-text-secondary-dark transition-colors hover:bg-black/5 dark:hover:bg-white/5">
                <span className="material-symbols-outlined text-base">chevron_right</span>
              </button>
            </div>
          </div>
        </div>
      </section>
    </div>
  );
};