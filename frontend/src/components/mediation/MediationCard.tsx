import { Link } from 'react-router-dom';
import { Mediation } from '../../types/mediation';

const statusMeta: Record<
  Mediation['status'],
  {
    label: string;
    className: string;
  }
> = {
  devam: { label: 'Devam Ediyor', className: 'bg-blue-100 text-blue-700' },
  anlasma: { label: 'Anlaşma Sağlandı', className: 'bg-green-100 text-green-700' },
  anlasmadi: { label: 'Anlaşma Sağlanamadı', className: 'bg-red-100 text-red-700' },
};

const formatDate = (value?: string) => {
  if (!value) {
    return '-';
  }
  return new Date(value).toLocaleDateString('tr-TR', { day: '2-digit', month: 'short', year: 'numeric' });
};

const partyNames = (parties: Mediation['applicants']) => parties.map((party) => party.name).join(', ');

interface Props {
  mediation: Mediation;
}

export const MediationCard = ({ mediation }: Props) => {
  const status = statusMeta[mediation.status];

  return (
    <Link
      to={`/mediation/${mediation.id}`}
      className="block rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:shadow-md"
    >
      <div className="flex items-start justify-between gap-3">
        <div>
          <p className="text-xs uppercase tracking-wide text-slate-500">Başvuru No</p>
          <p className="text-lg font-semibold text-slate-900">{mediation.code}</p>
        </div>
        <span className={`inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ${status.className}`}>
          {status.label}
        </span>
      </div>

      <div className="mt-4 space-y-3 text-sm text-slate-600">
        <div className="flex flex-wrap gap-2">
          <span className="font-semibold text-slate-500">Başvuran:</span>
          <span>{partyNames(mediation.applicants)}</span>
        </div>
        <div className="flex flex-wrap gap-2">
          <span className="font-semibold text-slate-500">Karşı taraf:</span>
          <span>{partyNames(mediation.respondents)}</span>
        </div>
        <div className="flex flex-wrap gap-2">
          <span className="font-semibold text-slate-500">Konu:</span>
          <span className="text-slate-900">{mediation.subject}</span>
        </div>
      </div>

      <div className="mt-4 flex items-center justify-between text-xs text-slate-500">
        <div className="flex items-center gap-2">
          <span className="material-symbols-outlined text-base text-slate-400">schedule</span>
          Son toplantı: <span className="font-semibold text-slate-700">{formatDate(mediation.lastMeetingDate)}</span>
        </div>
        <span className="inline-flex items-center gap-1 text-[#2463eb] font-semibold">
          Detaya git
          <span className="material-symbols-outlined text-base">arrow_forward</span>
        </span>
      </div>
    </Link>
  );
};
