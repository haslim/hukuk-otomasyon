import { useMemo, useState } from 'react';
import { Link, useParams } from 'react-router-dom';
import { CaseApi } from '../../api/modules/cases';
import { DocumentApi } from '../../api/modules/documents';
import { FinanceApi } from '../../api/modules/finance';
import { useAsyncData } from '../../hooks/useAsyncData';

export const CaseDetailPage = () => {
  const { id = '' } = useParams();
  const { data, isLoading } = useAsyncData(['case', id], () => CaseApi.show(id), {
    queryKey: ['case', id],
    enabled: Boolean(id),
  });
  const [activeTab, setActiveTab] = useState<'summary' | 'hearings' | 'tasks' | 'documents' | 'workflow' | 'finance'>(
    'summary',
  );

  const caseData = data ?? {};
  const clientName = caseData.client?.name ?? 'Müvekkil Belirtilmedi';
  const caseNo = caseData.case_no ?? id;
  const title = caseData.title ?? 'Dosya Başlığı';
  const court = caseData.metadata?.court ?? 'Mahkeme bilgisi yok';
  const status = caseData.metadata?.status ?? 'Aktif';
  const responsible = caseData.metadata?.responsible ?? 'Atanmamış';
  const summary = caseData.subject ?? 'Henüz bir özet girilmemiş.';

  const colorMap = {
    blue: { bg: 'bg-blue-100', text: 'text-blue-600' },
    red: { bg: 'bg-red-100', text: 'text-red-600' },
    slate: { bg: 'bg-slate-100', text: 'text-slate-600' },
  };

  const parties = useMemo(() => {
    if (!caseData.parties?.length) {
      return [
        { role: 'Davacı', name: clientName, lawyer: 'Belirtilmedi', color: 'blue' },
        { role: 'Davalı', name: 'Karşı taraf', lawyer: 'Belirtilmedi', color: 'red' },
      ];
    }
    return caseData.parties.map((party: any, idx: number) => ({
      role: party.role ?? (idx === 0 ? 'Davacı' : 'Davalı'),
      name: party.name ?? 'Taraf',
      lawyer: party.lawyer ?? 'Vekil bilgisi yok',
      color: idx === 0 ? 'blue' : 'red',
    }));
  }, [caseData.parties, clientName]);

  const {
    data: documents = [],
    isLoading: isDocumentsLoading,
  } = useAsyncData(
    ['case-documents', id],
    () => DocumentApi.listByCase(id),
    { queryKey: ['case-documents', id], enabled: activeTab === 'documents' && Boolean(id) },
  );

  const {
    data: cashTransactions = [],
    isLoading: isFinanceLoading,
  } = useAsyncData(
    ['case-finance', id],
    () => FinanceApi.getCashTransactions(),
    { queryKey: ['case-finance', id], enabled: activeTab === 'finance' },
  );

  const caseTransactions = useMemo(
    () => cashTransactions.filter((tx: any) => tx.caseNumber === caseNo || tx.caseNumber === title),
    [cashTransactions, caseNo, title],
  );

  if (isLoading) {
    return <p className="text-sm text-[#A0AEC0]">Dosya bilgileri yükleniyor...</p>;
  }

  return (
    <div className="space-y-6">
      <div className="flex items-center gap-2 text-sm">
        <Link to="/cases" className="text-[#A0AEC0] hover:text-[#2463eb]">
          Dosyalar
        </Link>
        <span className="material-symbols-outlined text-[#A0AEC0] text-base">chevron_right</span>
        <span className="text-[#1A202C] font-medium">{caseNo}</span>
      </div>

      <div className="flex flex-wrap items-start justify-between gap-4">
        <div>
          <p className="text-3xl font-bold text-[#1A202C]">Dava Dosyası: {caseNo}</p>
          <p className="text-[#4A5568]">
            Müvekkil: {clientName} | Mahkeme: {court}
          </p>
        </div>
        <div className="flex items-center gap-2">
          <button className="flex h-9 items-center gap-2 rounded-lg bg-slate-200 px-4 text-sm font-bold text-slate-800 hover:bg-slate-300">
            <span className="material-symbols-outlined text-base">edit</span>
            Düzenle
          </button>
          <button className="flex h-9 items-center gap-2 rounded-lg bg-[#2463eb] px-4 text-sm font-bold text-white hover:bg-[#1d4fd8]">
            <span className="material-symbols-outlined text-base">update</span>
            Durumu Güncelle
          </button>
        </div>
      </div>

      <div className="flex flex-wrap gap-3">
        <div className="flex h-8 items-center gap-2 rounded-full bg-green-100 px-3">
          <span className="size-2 rounded-full bg-green-500" />
          <p className="text-green-800 text-sm font-medium">Durum: {status}</p>
        </div>
        <div className="flex h-8 items-center gap-2 rounded-full bg-slate-100 px-3">
          <span className="material-symbols-outlined text-slate-600 text-base">person</span>
          <p className="text-slate-700 text-sm font-medium">Sorumlu: {responsible}</p>
        </div>
      </div>

      <div className="border-b border-slate-200">
        <div className="-mb-px flex space-x-6">
          {[
            { key: 'summary', label: 'Özet' },
            { key: 'hearings', label: 'Duruşmalar' },
            { key: 'tasks', label: 'Görevler' },
            { key: 'documents', label: 'Dokümanlar' },
            { key: 'workflow', label: 'Workflow' },
            { key: 'finance', label: 'Kasa' },
          ].map((tab) => (
            <button
              key={tab.key}
              className={`px-1 pb-3 text-sm ${
                activeTab === tab.key
                  ? 'border-b-2 border-[#2463eb] font-semibold text-[#2463eb]'
                  : 'text-[#A0AEC0] hover:text-[#4A5568]'
              }`}
              type="button"
              onClick={() => setActiveTab(tab.key as typeof activeTab)}
            >
              {tab.label}
            </button>
          ))}
        </div>
      </div>

      <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div className="flex flex-col gap-6 lg:col-span-2">
          {activeTab === 'summary' && (
            <>
              <section className="rounded-xl border border-slate-200 bg-white p-6">
                <h3 className="text-lg font-semibold text-[#1A202C]">Dava Özeti</h3>
                <p className="mt-2 text-sm text-[#4A5568] leading-relaxed">{summary}</p>
              </section>

              <section className="rounded-xl border border-slate-200 bg-white p-6">
                <h3 className="text-lg font-semibold text-[#1A202C]">Taraflar</h3>
                <ul className="mt-4 space-y-4">
                  {parties.map((party: any, idx: number) => (
                    <li key={party.name + idx} className="flex items-start gap-4">
                      <div
                        className={`flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full ${
                          colorMap[party.color as keyof typeof colorMap]?.bg ?? 'bg-slate-100'
                        } ${colorMap[party.color as keyof typeof colorMap]?.text ?? 'text-slate-600'} font-semibold`}
                      >
                        {party.role[0]}
                      </div>
                      <div>
                        <p className="font-medium text-[#1A202C]">
                          {party.role}: {party.name}
                        </p>
                        <p className="text-sm text-[#4A5568]">Vekil: {party.lawyer}</p>
                      </div>
                    </li>
                  ))}
                </ul>
              </section>
            </>
          )}

          {activeTab === 'hearings' && (
            <section className="rounded-xl border border-slate-200 bg-white p-6">
              <h3 className="text-lg font-semibold text-[#1A202C]">Duruşmalar</h3>
              <p className="mt-2 text-sm text-[#4A5568]">
                Bu sürümde duruşma listesi sadece bilgi amaçlıdır. Henüz bu dosya için duruşma eklenmemiş.
              </p>
            </section>
          )}

          {activeTab === 'tasks' && (
            <section className="rounded-xl border border-slate-200 bg-white p-6">
              <h3 className="text-lg font-semibold text-[#1A202C]">Görevler</h3>
              <p className="mt-2 text-sm text-[#4A5568]">
                Görev modülü için API entegrasyonu henüz tamamlanmadı. Şimdilik görevler burada listelenmeyecek.
              </p>
            </section>
          )}

          {activeTab === 'documents' && (
            <section className="rounded-xl border border-slate-200 bg-white p-6">
              <h3 className="text-lg font-semibold text-[#1A202C]">Dokümanlar</h3>
              {isDocumentsLoading ? (
                <p className="mt-2 text-sm text-[#4A5568]">Dokümanlar yükleniyor...</p>
              ) : documents.length === 0 ? (
                <p className="mt-2 text-sm text-[#4A5568]">Bu dosyaya ait doküman bulunmuyor.</p>
              ) : (
                <ul className="mt-4 space-y-3">
                  {documents.map((doc: any) => (
                    <li
                      key={doc.id}
                      className="flex items-center justify-between rounded-lg border border-slate-200 px-4 py-2"
                    >
                      <div>
                        <p className="text-sm font-medium text-[#1A202C]">{doc.filename ?? doc.name}</p>
                        <p className="text-xs text-[#718096]">{doc.mime_type ?? doc.type}</p>
                      </div>
                      <span className="material-symbols-outlined text-[#A0AEC0] text-base">description</span>
                    </li>
                  ))}
                </ul>
              )}
            </section>
          )}

          {activeTab === 'workflow' && (
            <section className="rounded-xl border border-slate-200 bg-white p-6">
              <h3 className="text-lg font-semibold text-[#1A202C]">Workflow</h3>
              {caseData.metadata?.workflow ? (
                <pre className="mt-3 whitespace-pre-wrap rounded-lg bg-slate-50 p-3 text-xs text-[#4A5568]">
                  {JSON.stringify(caseData.metadata.workflow, null, 2)}
                </pre>
              ) : (
                <p className="mt-2 text-sm text-[#4A5568]">
                  Bu dosyaya henüz bir workflow şablonu atanmadı.
                </p>
              )}
            </section>
          )}

          {activeTab === 'finance' && (
            <section className="rounded-xl border border-slate-200 bg-white p-6">
              <h3 className="text-lg font-semibold text-[#1A202C]">Kasa Hareketleri</h3>
              {isFinanceLoading ? (
                <p className="mt-2 text-sm text-[#4A5568]">Kasa hareketleri yükleniyor...</p>
              ) : caseTransactions.length === 0 ? (
                <p className="mt-2 text-sm text-[#4A5568]">
                  Bu dosya ile ilişkilendirilmiş bir kasa hareketi bulunmuyor.
                </p>
              ) : (
                <div className="mt-4 space-y-2">
                  {caseTransactions.map((tx: any) => (
                    <div
                      key={tx.id}
                      className="flex items-center justify-between rounded-lg border border-slate-200 px-4 py-2 text-sm"
                    >
                      <div>
                        <p className="font-medium text-[#1A202C]">
                          {tx.type === 'income' ? 'Gelir' : 'Gider'} - {tx.category}
                        </p>
                        <p className="text-xs text-[#718096]">{tx.date}</p>
                      </div>
                      <p className={`font-semibold ${tx.type === 'income' ? 'text-emerald-600' : 'text-red-600'}`}>
                        {tx.type === 'income' ? '+' : '-'}
                        {' '}
                        {tx.amount.toLocaleString('tr-TR', { style: 'currency', currency: 'TRY' })}
                      </p>
                    </div>
                  ))}
                </div>
              )}
            </section>
          )}
        </div>

        <aside className="space-y-6">
          <section className="rounded-xl border border-slate-200 bg-white p-6">
            <h3 className="text-lg font-semibold text-[#1A202C]">Önemli Tarihler</h3>
            <ul className="mt-4 space-y-4">
              {[
                { label: 'Dava Açılış', value: caseData.metadata?.opened_at ?? '—', icon: 'event' },
                { label: 'İlk Duruşma', value: caseData.metadata?.first_hearing ?? '—', icon: 'gavel' },
                { label: 'Cevap Süresi', value: caseData.metadata?.deadline ?? '—', icon: 'hourglass_top' },
              ].map((item) => (
                <li key={item.label} className="flex items-center gap-4">
                  <div className="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg bg-slate-100">
                    <span className="material-symbols-outlined text-slate-500 text-base">{item.icon}</span>
                  </div>
                  <div>
                    <p className="text-sm font-medium text-[#1A202C]">{item.label}</p>
                    <p className="text-sm text-[#4A5568]">{item.value}</p>
                  </div>
                </li>
              ))}
            </ul>
          </section>
        </aside>
      </div>
    </div>
  );
};
