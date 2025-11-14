import { useEffect, useMemo, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { Mediation } from '../../types/mediation';
import { mediationService } from '../../services/mediationService';
import { MediationCard } from '../../components/mediation/MediationCard';

type StatusFilter = 'tum' | Mediation['status'];
type DateFilter = 'all' | '7' | '30' | 'year';

const statusLabels: Record<Mediation['status'], { label: string; className: string }> = {
  devam: { label: 'Devam Ediyor', className: 'bg-blue-100 text-blue-700' },
  anlasma: { label: 'Anlaşma Sağlandı', className: 'bg-green-100 text-green-700' },
  anlasmadi: { label: 'Anlaşma Sağlanamadı', className: 'bg-red-100 text-red-700' },
};

const formatDate = (value: string) =>
  new Date(value).toLocaleDateString('tr-TR', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  });

const matchDateFilter = (mediation: Mediation, dateFilter: DateFilter) => {
  if (dateFilter === 'all') return true;
  const current = new Date();
  const applicationDate = new Date(mediation.applicationDate);
  if (dateFilter === 'year') {
    return applicationDate.getFullYear() === current.getFullYear();
  }
  const days = Number(dateFilter);
  const diff = current.getTime() - applicationDate.getTime();
  return diff <= days * 24 * 60 * 60 * 1000;
};

export const MediationListPage = () => {
  const navigate = useNavigate();
  const [mediations, setMediations] = useState<Mediation[]>([]);
  const [search, setSearch] = useState('');
  const [statusFilter, setStatusFilter] = useState<StatusFilter>('tum');
  const [dateFilter, setDateFilter] = useState<DateFilter>('all');

  useEffect(() => {
    mediationService.getMediations().then((data) => setMediations(data));
  }, []);

  const filtered = useMemo(() => {
    return mediations.filter((item) => {
      const haystack = `${item.subject} ${item.applicants.map((a) => a.name).join(' ')} ${item.respondents
        .map((r) => r.name)
        .join(' ')}`.toLowerCase();
      const matchesSearch = haystack.includes(search.toLowerCase());
      const matchesStatus = statusFilter === 'tum' || item.status === statusFilter;
      const matchesDate = matchDateFilter(item, dateFilter);
      return matchesSearch && matchesStatus && matchesDate;
    });
  }, [mediations, search, statusFilter, dateFilter]);

  return (
    <section className="space-y-6">
      <div className="flex flex-wrap items-center justify-between gap-4">
        <div>
          <h1 className="text-3xl font-bold text-slate-900">Arabuluculuk Başvuruları</h1>
          <p className="text-sm text-slate-500">Başvurularınızı durum, taraf ve konuya göre takip edin.</p>
        </div>
        <button
          onClick={() => navigate('/mediation/new')}
          className="inline-flex items-center gap-2 rounded-2xl bg-[#2463eb] px-5 py-3 font-semibold text-white hover:bg-[#1d4fd8]"
        >
          <span className="material-symbols-outlined text-base">add</span>
          Yeni Arabuluculuk Başvurusu
        </button>
      </div>

      <div className="grid gap-4 rounded-3xl border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-4">
        <div className="md:col-span-2">
          <label className="text-sm font-semibold text-slate-500">Arama</label>
          <div className="mt-1 flex items-center gap-2 rounded-2xl border border-slate-200 px-3">
            <span className="material-symbols-outlined text-slate-400">search</span>
            <input
              value={search}
              onChange={(event) => setSearch(event.target.value)}
              className="w-full border-none bg-transparent py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none"
              placeholder="Başvuran, karşı taraf, konu ara..."
            />
          </div>
        </div>
        <div>
          <label className="text-sm font-semibold text-slate-500">Durum</label>
          <select
            value={statusFilter}
            onChange={(event) => setStatusFilter(event.target.value as StatusFilter)}
            className="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#2463eb]"
          >
            <option value="tum">Tümü</option>
            <option value="devam">Devam Ediyor</option>
            <option value="anlasma">Anlaşma Sağlandı</option>
            <option value="anlasmadi">Anlaşma Sağlanamadı</option>
          </select>
        </div>
        <div>
          <label className="text-sm font-semibold text-slate-500">Tarih Aralığı</label>
          <select
            value={dateFilter}
            onChange={(event) => setDateFilter(event.target.value as DateFilter)}
            className="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#2463eb]"
          >
            <option value="all">Tümü</option>
            <option value="7">Son 7 gün</option>
            <option value="30">Son 30 gün</option>
            <option value="year">Bu yıl</option>
          </select>
        </div>
      </div>

      <div className="hidden rounded-3xl border border-slate-200 bg-white shadow-sm md:block">
        <table className="min-w-full text-left text-sm">
          <thead className="border-b border-slate-100 bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
            <tr>
              <th className="px-6 py-3">Başvuru No</th>
              <th className="px-6 py-3">Başvuran Taraf</th>
              <th className="px-6 py-3">Karşı Taraf</th>
              <th className="px-6 py-3">Konu</th>
              <th className="px-6 py-3">Durum</th>
              <th className="px-6 py-3">Son Toplantı</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-slate-100 text-slate-600">
            {filtered.map((item) => (
              <tr
                key={item.id}
                className="cursor-pointer transition hover:bg-slate-50"
                onClick={() => navigate(`/mediation/${item.id}`)}
              >
                <td className="px-6 py-4 font-semibold text-slate-900">{item.code}</td>
                <td className="px-6 py-4">{item.applicants.map((party) => party.name).join(', ')}</td>
                <td className="px-6 py-4">{item.respondents.map((party) => party.name).join(', ')}</td>
                <td className="px-6 py-4">{item.subject}</td>
                <td className="px-6 py-4">
                  <span
                    className={`inline-flex rounded-full px-3 py-1 text-xs font-semibold ${statusLabels[item.status].className}`}
                  >
                    {statusLabels[item.status].label}
                  </span>
                </td>
                <td className="px-6 py-4">{item.lastMeetingDate ? formatDate(item.lastMeetingDate) : '-'}</td>
              </tr>
            ))}
          </tbody>
        </table>
        {filtered.length === 0 && <p className="p-6 text-center text-sm text-slate-500">Kayıt bulunamadı.</p>}
      </div>

      <div className="space-y-3 md:hidden">
        {filtered.map((item) => (
          <MediationCard key={item.id} mediation={item} />
        ))}
        {filtered.length === 0 && <p className="text-center text-sm text-slate-500">Kayıt bulunamadı.</p>}
      </div>
    </section>
  );
};
