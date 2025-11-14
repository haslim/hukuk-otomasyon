import { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import { useParams } from 'react-router-dom';
import { mediationService } from '../../services/mediationService';
import { Mediation, MediationMeeting, MediationParty } from '../../types/mediation';
import { MediationDetailHeader } from '../../components/mediation/MediationDetailHeader';
import { MeetingModal } from '../../components/mediation/MeetingModal';
import { PartyCard } from '../../components/mediation/PartyCard';
import { DocumentList } from '../../components/mediation/DocumentList';
import { NoteCard } from '../../components/mediation/NoteCard';

type TabKey = 'summary' | 'meetings' | 'parties' | 'documents' | 'notes';

const tabs: { key: TabKey; label: string; icon: string }[] = [
  { key: 'summary', label: 'Özet', icon: 'dashboard' },
  { key: 'meetings', label: 'Toplantılar', icon: 'event' },
  { key: 'parties', label: 'Taraflar', icon: 'group' },
  { key: 'documents', label: 'Dokümanlar', icon: 'description' },
  { key: 'notes', label: 'Notlar', icon: 'sticky_note_2' },
];

const templateOptions = [
  { key: 'basvuru', label: 'Başvuru Formu' },
  { key: 'ilk', label: 'İlk Toplantı Tutanağı' },
  { key: 'son', label: 'Son Tutanak' },
  { key: 'anlasma', label: 'Anlaşma Belgesi' },
];

const defaultPartyForm = {
  type: 'basvuran' as MediationParty['type'],
  name: '',
  identifier: '',
  phone: '',
  email: '',
  roleDescription: '',
};

export const MediationDetailPage = () => {
  const { id } = useParams<{ id: string }>();
  const [loading, setLoading] = useState(true);
  const [mediation, setMediation] = useState<Mediation | null>(null);
  const [activeTab, setActiveTab] = useState<TabKey>('summary');
  const [meetingModalOpen, setMeetingModalOpen] = useState(false);
  const [editingMeeting, setEditingMeeting] = useState<MediationMeeting | null>(null);
  const [templateMenu, setTemplateMenu] = useState(false);
  const [noteContent, setNoteContent] = useState('');
  const [partyFormOpen, setPartyFormOpen] = useState(false);
  const [partyForm, setPartyForm] = useState(defaultPartyForm);
  const fileInputRef = useRef<HTMLInputElement | null>(null);

  const fetchMediation = useCallback(async () => {
    if (!id) return;
    setLoading(true);
    const data = await mediationService.getMediationById(id);
    setMediation(data);
    setLoading(false);
  }, [id]);

  useEffect(() => {
    fetchMediation();
  }, [fetchMediation]);

  const handleMeetingSave = async (payload: Omit<MediationMeeting, 'id'>) => {
    if (!id) return;
    await mediationService.addMeeting(id, payload);
    setMeetingModalOpen(false);
    setEditingMeeting(null);
    fetchMediation();
  };

  const handlePartySave = async () => {
    if (!id || !partyForm.name) return;
    await mediationService.addParty(id, {
      ...partyForm,
    });
    setPartyForm(defaultPartyForm);
    setPartyFormOpen(false);
    fetchMediation();
  };

  const handleDocumentUpload = async (fileName: string, type: string) => {
    if (!id) return;
    await mediationService.uploadDocument(id, {
      name: fileName,
      type,
      uploadedBy: 'BGA Kullanıcısı',
    });
    fetchMediation();
  };

  const handleTemplateGenerate = async (templateKey: string) => {
    const template = templateOptions.find((item) => item.key === templateKey);
    if (!template) return;
    await handleDocumentUpload(template.label, 'Şablon');
    setTemplateMenu(false);
  };

  const handleNoteAdd = async () => {
    if (!id || !noteContent.trim()) return;
    await mediationService.addNote(id, {
      author: 'BGA Kullanıcısı',
      content: noteContent.trim(),
    });
    setNoteContent('');
    fetchMediation();
  };

  const summaryInfo = useMemo(() => {
    if (!mediation) return [];
    return [
      {
        label: 'Başvuran Taraf(lar)',
        value: mediation.applicants.map((party) => party.name).join(', '),
      },
      {
        label: 'Karşı Taraf(lar)',
        value: mediation.respondents.map((party) => party.name).join(', '),
      },
      {
        label: 'Başvuru Tarihi',
        value: new Date(mediation.applicationDate).toLocaleDateString('tr-TR'),
      },
      { label: 'Atanan Avukat', value: mediation.assignedLawyer },
    ];
  }, [mediation]);

  if (loading) {
    return <p className="text-center text-sm text-slate-500">Yükleniyor...</p>;
  }

  if (!mediation) {
    return <p className="text-center text-sm text-red-500">Kayıt bulunamadı.</p>;
  }

  return (
    <section className="space-y-6">
      <MediationDetailHeader mediation={mediation} />

      <div className="rounded-3xl border border-slate-200 bg-white p-2 shadow-sm">
        <div className="flex flex-wrap gap-2">
          {tabs.map((tab) => (
            <button
              key={tab.key}
              onClick={() => setActiveTab(tab.key)}
              className={`flex flex-1 items-center justify-center gap-2 rounded-2xl px-4 py-2 text-sm font-semibold transition ${
                activeTab === tab.key ? 'bg-[#2463eb] text-white' : 'text-slate-500 hover:bg-slate-50'
              }`}
            >
              <span className="material-symbols-outlined text-base">{tab.icon}</span>
              {tab.label}
            </button>
          ))}
        </div>
      </div>

      {activeTab === 'summary' && (
        <div className="grid gap-4 lg:grid-cols-3">
          <div className="space-y-4 lg:col-span-2">
            <div className="rounded-3xl border border-slate-200 bg-white p-4">
              <h3 className="text-lg font-semibold text-slate-900">Temel Bilgiler</h3>
              <dl className="mt-4 grid gap-4 sm:grid-cols-2">
                {summaryInfo.map((info) => (
                  <div key={info.label}>
                    <dt className="text-xs uppercase tracking-wide text-slate-400">{info.label}</dt>
                    <dd className="text-base font-semibold text-slate-900">{info.value || '-'}</dd>
                  </div>
                ))}
              </dl>
            </div>
            {mediation.requests && mediation.requests.length > 0 && (
              <div className="rounded-3xl border border-slate-200 bg-white p-4">
                <h3 className="text-lg font-semibold text-slate-900">Talepler</h3>
                <ul className="mt-3 list-disc space-y-2 pl-6 text-sm text-slate-600">
                  {mediation.requests.map((request, index) => (
                    <li key={`${request}-${index}`}>{request}</li>
                  ))}
                </ul>
              </div>
            )}
          </div>
          <div className="rounded-3xl border border-slate-200 bg-white p-4">
            <h3 className="text-lg font-semibold text-slate-900">Hızlı Özet</h3>
            <ul className="mt-4 space-y-3 text-sm text-slate-600">
              <li className="flex items-center justify-between">
                <span>Toplam toplantı</span>
                <strong className="text-slate-900">{mediation.meetings.length}</strong>
              </li>
              <li className="flex items-center justify-between">
                <span>Doküman sayısı</span>
                <strong className="text-slate-900">{mediation.documents.length}</strong>
              </li>
              <li className="flex items-center justify-between">
                <span>Not sayısı</span>
                <strong className="text-slate-900">{mediation.notes.length}</strong>
              </li>
            </ul>
          </div>
        </div>
      )}

      {activeTab === 'meetings' && (
        <div className="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
          <div className="flex flex-wrap items-center justify-between gap-3">
            <h3 className="text-lg font-semibold text-slate-900">Toplantılar</h3>
            <button
              onClick={() => {
                setMeetingModalOpen(true);
                setEditingMeeting(null);
              }}
              className="inline-flex items-center gap-2 rounded-2xl bg-[#2463eb] px-4 py-2 text-sm font-semibold text-white hover:bg-[#1d4fd8]"
            >
              <span className="material-symbols-outlined text-base">add</span>
              Toplantı Ekle
            </button>
          </div>
          <div className="mt-4 overflow-x-auto">
            <table className="min-w-full text-sm">
              <thead className="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr>
                  <th className="px-4 py-3">Tarih & Saat</th>
                  <th className="px-4 py-3">Yer</th>
                  <th className="px-4 py-3">Katılanlar</th>
                  <th className="px-4 py-3">Sonuç</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-100">
                {mediation.meetings.map((meeting) => (
                  <tr
                    key={meeting.id}
                    onClick={() => {
                      setEditingMeeting(meeting);
                      setMeetingModalOpen(true);
                    }}
                    className="cursor-pointer text-slate-600 transition hover:bg-slate-50"
                  >
                    <td className="px-4 py-3 font-semibold text-slate-900">
                      {meeting.date} – {meeting.time}
                    </td>
                    <td className="px-4 py-3">{meeting.location}</td>
                    <td className="px-4 py-3">{meeting.attendees.join(', ')}</td>
                    <td className="px-4 py-3">{meeting.result}</td>
                  </tr>
                ))}
              </tbody>
            </table>
            {mediation.meetings.length === 0 && (
              <p className="py-6 text-center text-sm text-slate-500">Toplantı bulunamadı.</p>
            )}
          </div>
        </div>
      )}

      {activeTab === 'parties' && (
        <div className="space-y-4">
          <div className="flex flex-wrap items-center justify-between gap-3">
            <h3 className="text-lg font-semibold text-slate-900">Taraflar</h3>
            <button
              onClick={() => setPartyFormOpen((prev) => !prev)}
              className="inline-flex items-center gap-2 rounded-2xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
            >
              <span className="material-symbols-outlined text-base">group_add</span>
              Taraf Ekle
            </button>
          </div>
          {partyFormOpen && (
            <div className="rounded-3xl border border-slate-200 bg-white p-4">
              <div className="grid gap-4 md:grid-cols-2">
                <label className="text-sm text-slate-600">
                  Taraf Türü
                  <select
                    value={partyForm.type}
                    onChange={(event) =>
                      setPartyForm((prev) => ({ ...prev, type: event.target.value as MediationParty['type'] }))
                    }
                    className="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#2463eb]"
                  >
                    <option value="basvuran">Başvuran</option>
                    <option value="karsi">Karşı Taraf</option>
                  </select>
                </label>
                <label className="text-sm text-slate-600">
                  Ad / Unvan
                  <input
                    value={partyForm.name}
                    onChange={(event) => setPartyForm((prev) => ({ ...prev, name: event.target.value }))}
                    className="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#2463eb]"
                  />
                </label>
                <label className="text-sm text-slate-600">
                  TCKN / VKN
                  <input
                    value={partyForm.identifier}
                    onChange={(event) => setPartyForm((prev) => ({ ...prev, identifier: event.target.value }))}
                    className="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#2463eb]"
                  />
                </label>
                <label className="text-sm text-slate-600">
                  Telefon
                  <input
                    value={partyForm.phone}
                    onChange={(event) => setPartyForm((prev) => ({ ...prev, phone: event.target.value }))}
                    className="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#2463eb]"
                  />
                </label>
                <label className="text-sm text-slate-600">
                  E-posta
                  <input
                    value={partyForm.email}
                    onChange={(event) => setPartyForm((prev) => ({ ...prev, email: event.target.value }))}
                    className="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#2463eb]"
                  />
                </label>
                <label className="text-sm text-slate-600 md:col-span-2">
                  Rol / Açıklama
                  <textarea
                    rows={3}
                    value={partyForm.roleDescription}
                    onChange={(event) => setPartyForm((prev) => ({ ...prev, roleDescription: event.target.value }))}
                    className="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#2463eb]"
                  />
                </label>
              </div>
              <div className="mt-4 flex justify-end gap-3">
                <button
                  type="button"
                  onClick={() => {
                    setPartyForm(defaultPartyForm);
                    setPartyFormOpen(false);
                  }}
                  className="rounded-2xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50"
                >
                  İptal
                </button>
                <button
                  type="button"
                  onClick={handlePartySave}
                  className="rounded-2xl bg-[#2463eb] px-4 py-2 text-sm font-semibold text-white hover:bg-[#1d4fd8]"
                >
                  Kaydet
                </button>
              </div>
            </div>
          )}
          <div className="grid gap-4 md:grid-cols-2">
            {mediation.applicants.map((party) => (
              <PartyCard key={party.id} party={party} />
            ))}
            {mediation.respondents.map((party) => (
              <PartyCard key={party.id} party={party} />
            ))}
          </div>
        </div>
      )}

      {activeTab === 'documents' && (
        <div className="space-y-4">
          <div className="flex flex-wrap items-center gap-3">
            <button
              onClick={() => fileInputRef.current?.click()}
              className="inline-flex items-center gap-2 rounded-2xl bg-[#2463eb] px-4 py-2 text-sm font-semibold text-white hover:bg-[#1d4fd8]"
            >
              <span className="material-symbols-outlined text-base">upload</span>
              Doküman Yükle
            </button>
            <div className="relative">
              <button
                onClick={() => setTemplateMenu((prev) => !prev)}
                className="inline-flex items-center gap-2 rounded-2xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
              >
                <span className="material-symbols-outlined text-base">contract</span>
                Şablondan Belge Oluştur
                <span className="material-symbols-outlined text-base">expand_more</span>
              </button>
              {templateMenu && (
                <div className="absolute z-10 mt-2 w-64 rounded-2xl border border-slate-100 bg-white p-2 shadow-lg">
                  {templateOptions.map((template) => (
                    <button
                      key={template.key}
                      onClick={() => handleTemplateGenerate(template.key)}
                      className="flex w-full items-center gap-2 rounded-2xl px-3 py-2 text-left text-sm text-slate-600 hover:bg-slate-50"
                    >
                      <span className="material-symbols-outlined text-base text-slate-400">description</span>
                      {template.label}
                    </button>
                  ))}
                </div>
              )}
            </div>
            <input
              ref={fileInputRef}
              type="file"
              className="hidden"
              onChange={(event) => {
                const file = event.target.files?.[0];
                if (file) {
                  handleDocumentUpload(file.name, 'Yüklenen');
                  event.target.value = '';
                }
              }}
            />
          </div>
          <DocumentList documents={mediation.documents} />
        </div>
      )}

      {activeTab === 'notes' && (
        <div className="grid gap-4 lg:grid-cols-[1fr_350px]">
          <div className="space-y-4">
            {mediation.notes.map((note) => (
              <NoteCard key={note.id} note={note} />
            ))}
            {mediation.notes.length === 0 && (
              <p className="text-center text-sm text-slate-500">Henüz not bulunmuyor.</p>
            )}
          </div>
          <div className="rounded-3xl border border-slate-200 bg-white p-4">
            <h3 className="text-lg font-semibold text-slate-900">Yeni Not</h3>
            <textarea
              rows={6}
              value={noteContent}
              onChange={(event) => setNoteContent(event.target.value)}
              placeholder="İç notunuzu buraya yazın..."
              className="mt-3 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#2463eb]"
            />
            <button
              onClick={handleNoteAdd}
              className="mt-3 w-full rounded-2xl bg-[#2463eb] px-4 py-2 text-sm font-semibold text-white hover:bg-[#1d4fd8]"
            >
              Notu Kaydet
            </button>
          </div>
        </div>
      )}

      <MeetingModal
        open={meetingModalOpen}
        meeting={editingMeeting ?? undefined}
        onClose={() => {
          setMeetingModalOpen(false);
          setEditingMeeting(null);
        }}
        onSave={handleMeetingSave}
      />
    </section>
  );
};
