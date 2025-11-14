import { useMemo, useState } from 'react';
import { MediationParty, NewMediationPayload } from '../../types/mediation';
import { PartyCard } from './PartyCard';

interface Props {
  onSubmit: (payload: NewMediationPayload) => Promise<void> | void;
}

const stepItems = [
  { label: 'Temel Bilgiler', description: 'Konu, avukat ve tarih bilgileri' },
  { label: 'Başvuran Taraflar', description: 'Müvekkil taraf bilgileri' },
  { label: 'Karşı Taraflar', description: 'Muhatap kişi / kurum bilgileri' },
  { label: 'Konu ve Talepler', description: 'Uyuşmazlık detayları ve talepler' },
  { label: 'Önizleme & Kaydet', description: 'Son kontroller ve kayıt' },
];

const createId = () => Math.random().toString(36).substring(2, 9);

type PartyForm = Omit<MediationParty, 'id' | 'type'>;

export const MediationStepper = ({ onSubmit }: Props) => {
  const [step, setStep] = useState(0);
  const [submitting, setSubmitting] = useState(false);
  const [basicInfo, setBasicInfo] = useState({
    subject: '',
    description: '',
    assignedLawyer: '',
    applicationDate: '',
  });
  const [topicDetails, setTopicDetails] = useState('');
  const [requests, setRequests] = useState<string[]>([]);
  const [requestInput, setRequestInput] = useState('');
  const [applicants, setApplicants] = useState<MediationParty[]>([]);
  const [respondents, setRespondents] = useState<MediationParty[]>([]);
  const [applicantForm, setApplicantForm] = useState<PartyForm>({
    name: '',
    identifier: '',
    phone: '',
    email: '',
    roleDescription: '',
  });
  const [respondentForm, setRespondentForm] = useState<PartyForm>({
    name: '',
    identifier: '',
    phone: '',
    email: '',
    roleDescription: '',
  });

  const canProceed = useMemo(() => {
    switch (step) {
      case 0:
        return Boolean(basicInfo.subject && basicInfo.assignedLawyer && basicInfo.applicationDate);
      case 1:
        return applicants.length > 0;
      case 2:
        return respondents.length > 0;
      case 3:
        return requests.length > 0;
      default:
        return true;
    }
  }, [step, basicInfo, applicants, respondents, requests]);

  const goNext = () => {
    if (step < stepItems.length - 1) {
      setStep((prev) => prev + 1);
    }
  };

  const goBack = () => setStep((prev) => Math.max(prev - 1, 0));

  const handleAddParty = (type: MediationParty['type']) => {
    const form = type === 'basvuran' ? applicantForm : respondentForm;
    if (!form.name) {
      return;
    }
    const party: MediationParty = {
      ...form,
      id: createId(),
      type,
    };
    if (type === 'basvuran') {
      setApplicants((prev) => [...prev, party]);
      setApplicantForm({ name: '', identifier: '', phone: '', email: '', roleDescription: '' });
    } else {
      setRespondents((prev) => [...prev, party]);
      setRespondentForm({ name: '', identifier: '', phone: '', email: '', roleDescription: '' });
    }
  };

  const addRequest = () => {
    if (!requestInput.trim()) return;
    setRequests((prev) => [...prev, requestInput.trim()]);
    setRequestInput('');
  };

  const removeRequest = (index: number) => {
    setRequests((prev) => prev.filter((_, idx) => idx !== index));
  };

  const handleFinish = async () => {
    if (submitting) return;
    setSubmitting(true);
    await onSubmit({
      subject: basicInfo.subject,
      description: [basicInfo.description, topicDetails].filter(Boolean).join('\n\n'),
      assignedLawyer: basicInfo.assignedLawyer,
      applicationDate: basicInfo.applicationDate,
      applicants,
      respondents,
      requests,
    });
    setSubmitting(false);
  };

  const renderStepContent = () => {
    switch (step) {
      case 0:
        return (
          <div className="grid gap-4">
            <label className="flex flex-col gap-1 text-sm text-slate-600">
              Başvuru Konusu
              <input
                value={basicInfo.subject}
                onChange={(event) => setBasicInfo((prev) => ({ ...prev, subject: event.target.value }))}
                className="rounded-2xl border border-slate-200 px-4 py-3 text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#2463eb]"
                placeholder="Örn. İşçilik alacağı"
              />
            </label>
            <label className="flex flex-col gap-1 text-sm text-slate-600">
              Açıklama
              <textarea
                rows={4}
                value={basicInfo.description}
                onChange={(event) => setBasicInfo((prev) => ({ ...prev, description: event.target.value }))}
                className="rounded-2xl border border-slate-200 px-4 py-3 text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#2463eb]"
                placeholder="Uyuşmazlık ile ilgili kısa özet..."
              />
            </label>
            <div className="grid gap-4 md:grid-cols-2">
              <label className="flex flex-col gap-1 text-sm text-slate-600">
                Dosyaya Atanacak Avukat
                <input
                  value={basicInfo.assignedLawyer}
                  onChange={(event) => setBasicInfo((prev) => ({ ...prev, assignedLawyer: event.target.value }))}
                  className="rounded-2xl border border-slate-200 px-4 py-3 text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#2463eb]"
                  placeholder="Avukat adı"
                />
              </label>
              <label className="flex flex-col gap-1 text-sm text-slate-600">
                Başvuru Tarihi
                <input
                  type="date"
                  value={basicInfo.applicationDate}
                  onChange={(event) => setBasicInfo((prev) => ({ ...prev, applicationDate: event.target.value }))}
                  className="rounded-2xl border border-slate-200 px-4 py-3 text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#2463eb]"
                />
              </label>
            </div>
          </div>
        );
      case 1:
        return (
          <div className="space-y-6">
            <div className="grid gap-4 md:grid-cols-2">
              {applicants.map((party) => (
                <PartyCard key={party.id} party={party} />
              ))}
            </div>
            <div className="rounded-3xl border border-slate-200 bg-white/80 p-4">
              <h4 className="text-lg font-semibold text-slate-900">Yeni Başvuran Ekle</h4>
              <div className="mt-4 grid gap-4 md:grid-cols-2">
                <input
                  className="rounded-2xl border border-slate-200 px-4 py-2 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#2463eb]"
                  placeholder="Ad / Unvan"
                  value={applicantForm.name}
                  onChange={(event) => setApplicantForm((prev) => ({ ...prev, name: event.target.value }))}
                />
                <input
                  className="rounded-2xl border border-slate-200 px-4 py-2 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#2463eb]"
                  placeholder="TCKN / VKN"
                  value={applicantForm.identifier}
                  onChange={(event) => setApplicantForm((prev) => ({ ...prev, identifier: event.target.value }))}
                />
                <input
                  className="rounded-2xl border border-slate-200 px-4 py-2 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#2463eb]"
                  placeholder="Telefon"
                  value={applicantForm.phone}
                  onChange={(event) => setApplicantForm((prev) => ({ ...prev, phone: event.target.value }))}
                />
                <input
                  className="rounded-2xl border border-slate-200 px-4 py-2 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#2463eb]"
                  placeholder="E-posta"
                  value={applicantForm.email}
                  onChange={(event) => setApplicantForm((prev) => ({ ...prev, email: event.target.value }))}
                />
              </div>
              <textarea
                rows={3}
                className="mt-3 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#2463eb]"
                placeholder="Rol / Açıklama"
                value={applicantForm.roleDescription}
                onChange={(event) => setApplicantForm((prev) => ({ ...prev, roleDescription: event.target.value }))}
              />
              <button
                type="button"
                onClick={() => handleAddParty('basvuran')}
                className="mt-3 inline-flex items-center justify-center rounded-2xl bg-[#2463eb] px-4 py-2 text-sm font-semibold text-white hover:bg-[#1d4fd8]"
              >
                Başvuranı Ekle
              </button>
            </div>
          </div>
        );
      case 2:
        return (
          <div className="space-y-6">
            <div className="grid gap-4 md:grid-cols-2">
              {respondents.map((party) => (
                <PartyCard key={party.id} party={party} />
              ))}
            </div>
            <div className="rounded-3xl border border-slate-200 bg-white/80 p-4">
              <h4 className="text-lg font-semibold text-slate-900">Yeni Karşı Taraf Ekle</h4>
              <div className="mt-4 grid gap-4 md:grid-cols-2">
                <input
                  className="rounded-2xl border border-slate-200 px-4 py-2 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#2463eb]"
                  placeholder="Ad / Unvan"
                  value={respondentForm.name}
                  onChange={(event) => setRespondentForm((prev) => ({ ...prev, name: event.target.value }))}
                />
                <input
                  className="rounded-2xl border border-slate-200 px-4 py-2 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#2463eb]"
                  placeholder="TCKN / VKN"
                  value={respondentForm.identifier}
                  onChange={(event) => setRespondentForm((prev) => ({ ...prev, identifier: event.target.value }))}
                />
                <input
                  className="rounded-2xl border border-slate-200 px-4 py-2 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#2463eb]"
                  placeholder="Telefon"
                  value={respondentForm.phone}
                  onChange={(event) => setRespondentForm((prev) => ({ ...prev, phone: event.target.value }))}
                />
                <input
                  className="rounded-2xl border border-slate-200 px-4 py-2 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#2463eb]"
                  placeholder="E-posta"
                  value={respondentForm.email}
                  onChange={(event) => setRespondentForm((prev) => ({ ...prev, email: event.target.value }))}
                />
              </div>
              <textarea
                rows={3}
                className="mt-3 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#2463eb]"
                placeholder="Rol / Açıklama"
                value={respondentForm.roleDescription}
                onChange={(event) => setRespondentForm((prev) => ({ ...prev, roleDescription: event.target.value }))}
              />
              <button
                type="button"
                onClick={() => handleAddParty('karsi')}
                className="mt-3 inline-flex items-center justify-center rounded-2xl bg-[#2463eb] px-4 py-2 text-sm font-semibold text-white hover:bg-[#1d4fd8]"
              >
                Karşı Tarafı Ekle
              </button>
            </div>
          </div>
        );
      case 3:
        return (
          <div className="space-y-6">
            <label className="flex flex-col gap-1 text-sm text-slate-600">
              Konu Detayları
              <textarea
                rows={5}
                value={topicDetails}
                onChange={(event) => setTopicDetails(event.target.value)}
                className="rounded-3xl border border-slate-200 px-4 py-3 text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#2463eb]"
                placeholder="Uyuşmazlığın kapsamı, süreç bilgileri..."
              />
            </label>
            <div className="rounded-3xl border border-dashed border-[#2463eb] p-4">
              <h4 className="text-lg font-semibold text-slate-900">Talepler</h4>
              <div className="mt-3 flex flex-col gap-3 md:flex-row">
                <input
                  className="flex-1 rounded-2xl border border-slate-200 px-4 py-2 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#2463eb]"
                  placeholder="Talepleri girin ve ekleyin"
                  value={requestInput}
                  onChange={(event) => setRequestInput(event.target.value)}
                />
                <button
                  type="button"
                  onClick={addRequest}
                  className="inline-flex items-center justify-center rounded-2xl bg-[#2463eb] px-4 py-2 text-sm font-semibold text-white hover:bg-[#1d4fd8]"
                >
                  Talep Ekle
                </button>
              </div>
              <ul className="mt-4 space-y-2">
                {requests.map((request, index) => (
                  <li
                    key={`${request}-${index}`}
                    className="flex items-center justify-between rounded-2xl border border-slate-100 bg-slate-50 px-4 py-2 text-sm text-slate-700"
                  >
                    <span>{request}</span>
                    <button
                      type="button"
                      onClick={() => removeRequest(index)}
                      className="text-xs font-semibold text-red-500 hover:text-red-600"
                    >
                      Sil
                    </button>
                  </li>
                ))}
              </ul>
            </div>
          </div>
        );
      case 4:
        return (
          <div className="space-y-6">
            <div className="grid gap-4 md:grid-cols-2">
              <div className="rounded-3xl border border-slate-200 bg-white p-4">
                <h4 className="text-sm uppercase tracking-wide text-slate-400">Temel Bilgiler</h4>
                <p className="mt-2 text-lg font-semibold text-slate-900">{basicInfo.subject}</p>
                <p className="text-sm text-slate-500">{basicInfo.assignedLawyer}</p>
                <p className="text-sm text-slate-500">
                  {basicInfo.applicationDate &&
                    new Date(basicInfo.applicationDate).toLocaleDateString('tr-TR', {
                      day: '2-digit',
                      month: 'short',
                      year: 'numeric',
                    })}
                </p>
                {basicInfo.description && <p className="mt-3 text-sm text-slate-600">{basicInfo.description}</p>}
              </div>
              <div className="rounded-3xl border border-slate-200 bg-white p-4">
                <h4 className="text-sm uppercase tracking-wide text-slate-400">Konu Detayı</h4>
                <p className="mt-2 text-sm text-slate-600">{topicDetails || 'Bilgi girilmedi'}</p>
              </div>
            </div>
            <div className="grid gap-4 md:grid-cols-2">
              <div className="rounded-3xl border border-slate-200 bg-white p-4">
                <h4 className="text-sm uppercase tracking-wide text-slate-400">Başvuranlar</h4>
                <ul className="mt-2 space-y-2 text-sm text-slate-700">
                  {applicants.map((party) => (
                    <li key={party.id} className="rounded-2xl bg-slate-50 px-3 py-2">
                      {party.name} — {party.identifier}
                    </li>
                  ))}
                </ul>
              </div>
              <div className="rounded-3xl border border-slate-200 bg-white p-4">
                <h4 className="text-sm uppercase tracking-wide text-slate-400">Karşı Taraflar</h4>
                <ul className="mt-2 space-y-2 text-sm text-slate-700">
                  {respondents.map((party) => (
                    <li key={party.id} className="rounded-2xl bg-slate-50 px-3 py-2">
                      {party.name} — {party.identifier}
                    </li>
                  ))}
                </ul>
              </div>
            </div>
            <div className="rounded-3xl border border-slate-200 bg-white p-4">
              <h4 className="text-sm uppercase tracking-wide text-slate-400">Talepler</h4>
              <ul className="mt-2 list-disc space-y-1 pl-6 text-sm text-slate-700">
                {requests.map((request, index) => (
                  <li key={`${request}-${index}`}>{request}</li>
                ))}
              </ul>
            </div>
          </div>
        );
      default:
        return null;
    }
  };

  return (
    <div className="space-y-6 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
      <div className="flex flex-col gap-4">
        <div className="flex items-center justify-between">
          <div>
            <p className="text-xs uppercase tracking-wide text-slate-400">Adım {step + 1} / {stepItems.length}</p>
            <h3 className="text-2xl font-semibold text-slate-900">{stepItems[step].label}</h3>
            <p className="text-sm text-slate-500">{stepItems[step].description}</p>
          </div>
          <div className="flex items-center gap-2">
            {stepItems.map((item, index) => (
              <span
                key={item.label}
                className={`h-2 w-10 rounded-full ${index <= step ? 'bg-[#2463eb]' : 'bg-slate-200'}`}
              />
            ))}
          </div>
        </div>
        <div className="h-[1px] w-full bg-slate-100" />
      </div>

      {renderStepContent()}

      <div className="flex items-center justify-between pt-4">
        <button
          type="button"
          onClick={goBack}
          disabled={step === 0}
          className="inline-flex items-center gap-2 rounded-2xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 disabled:opacity-40"
        >
          <span className="material-symbols-outlined text-base">arrow_back</span>
          Geri
        </button>
        {step < stepItems.length - 1 ? (
          <button
            type="button"
            onClick={goNext}
            disabled={!canProceed}
            className="inline-flex items-center gap-2 rounded-2xl bg-[#2463eb] px-4 py-2 text-sm font-semibold text-white disabled:opacity-50"
          >
            İleri
            <span className="material-symbols-outlined text-base">arrow_forward</span>
          </button>
        ) : (
          <button
            type="button"
            onClick={handleFinish}
            disabled={!canProceed || submitting}
            className="inline-flex items-center gap-2 rounded-2xl bg-[#22c55e] px-6 py-2 text-sm font-semibold text-white disabled:opacity-50"
          >
            {submitting ? 'Kaydediliyor...' : 'Kaydet ve Oluştur'}
          </button>
        )}
      </div>
    </div>
  );
};
