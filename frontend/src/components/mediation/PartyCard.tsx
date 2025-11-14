import { MediationParty } from '../../types/mediation';

interface Props {
  party: MediationParty;
}

const labels: Record<MediationParty['type'], string> = {
  basvuran: 'Başvuran',
  karsi: 'Karşı Taraf',
};

export const PartyCard = ({ party }: Props) => (
  <article className="rounded-2xl border border-slate-200 bg-white/80 p-4 shadow-sm">
    <div className="flex items-center justify-between">
      <h4 className="text-lg font-semibold text-slate-900">{party.name}</h4>
      <span className="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
        {labels[party.type]}
      </span>
    </div>
    {party.roleDescription && <p className="mt-1 text-sm text-slate-500">{party.roleDescription}</p>}
    <dl className="mt-4 grid gap-2 text-sm text-slate-600">
      <div className="flex justify-between">
        <dt className="text-slate-500">TCKN / VKN</dt>
        <dd className="font-semibold text-slate-900">{party.identifier || '-'}</dd>
      </div>
      <div className="flex justify-between">
        <dt className="text-slate-500">Telefon</dt>
        <dd className="font-semibold text-slate-900">{party.phone || '-'}</dd>
      </div>
      <div className="flex justify-between">
        <dt className="text-slate-500">E-posta</dt>
        <dd className="font-semibold text-slate-900">{party.email || '-'}</dd>
      </div>
    </dl>
  </article>
);
