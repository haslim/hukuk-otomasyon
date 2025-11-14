import { Mediation } from '../../types/mediation';

const statusClasses: Record<Mediation['status'], string> = {
  devam: 'bg-blue-100 text-blue-700',
  anlasma: 'bg-green-100 text-green-700',
  anlasmadi: 'bg-red-100 text-red-700',
};

const statusLabels: Record<Mediation['status'], string> = {
  devam: 'Devam Ediyor',
  anlasma: 'Anlaşma Sağlandı',
  anlasmadi: 'Anlaşma Sağlanamadı',
};

interface Props {
  mediation: Mediation;
}

export const MediationDetailHeader = ({ mediation }: Props) => (
  <header className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
    <div className="flex flex-wrap items-center justify-between gap-4">
      <div>
        <p className="text-sm uppercase tracking-wide text-slate-500">Arabuluculuk</p>
        <h1 className="text-2xl font-bold text-slate-900">Arabuluculuk – {mediation.code}</h1>
        <p className="text-sm text-slate-500">Konu: {mediation.subject}</p>
      </div>
      <span
        className={`inline-flex items-center rounded-full px-4 py-1.5 text-sm font-semibold ${statusClasses[mediation.status]}`}
      >
        {statusLabels[mediation.status]}
      </span>
    </div>

    <div className="mt-6 grid gap-4 md:grid-cols-4 text-sm text-slate-600">
      <div>
        <p className="text-xs uppercase tracking-wide text-slate-400">Başvuran</p>
        <p className="font-semibold text-slate-900">{mediation.applicants.map((a) => a.name).join(', ')}</p>
      </div>
      <div>
        <p className="text-xs uppercase tracking-wide text-slate-400">Karşı Taraf</p>
        <p className="font-semibold text-slate-900">{mediation.respondents.map((a) => a.name).join(', ')}</p>
      </div>
      <div>
        <p className="text-xs uppercase tracking-wide text-slate-400">Başvuru Tarihi</p>
        <p className="font-semibold text-slate-900">
          {new Date(mediation.applicationDate).toLocaleDateString('tr-TR', {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
          })}
        </p>
      </div>
      <div>
        <p className="text-xs uppercase tracking-wide text-slate-400">Atanan Avukat</p>
        <p className="font-semibold text-slate-900">{mediation.assignedLawyer}</p>
      </div>
    </div>

    {mediation.description && (
      <p className="mt-4 text-sm leading-relaxed text-slate-600">{mediation.description}</p>
    )}
  </header>
);
