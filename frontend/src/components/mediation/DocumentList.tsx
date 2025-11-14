import { useState } from 'react';
import { MediationDocument } from '../../types/mediation';

interface Props {
  documents: MediationDocument[];
}

export const DocumentList = ({ documents }: Props) => {
  const [selected, setSelected] = useState<MediationDocument | null>(null);

  return (
    <>
      <div className="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm">
        {documents.length > 0 ? (
          <table className="min-w-full divide-y divide-slate-100 text-sm">
            <thead className="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
              <tr>
                <th className="px-4 py-3">Belge</th>
                <th className="px-4 py-3">Tür</th>
                <th className="px-4 py-3">Yükleyen</th>
                <th className="px-4 py-3">Tarih</th>
                <th className="px-4 py-3 text-right">İşlemler</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100 bg-white text-slate-600">
              {documents.map((document) => (
                <tr key={document.id}>
                  <td className="px-4 py-3">
                    <p className="font-semibold text-slate-900">{document.name}</p>
                    <p className="text-xs text-slate-400">Son versiyon: {document.versions[0]?.version}</p>
                  </td>
                  <td className="px-4 py-3">{document.type}</td>
                  <td className="px-4 py-3">{document.uploadedBy}</td>
                  <td className="px-4 py-3">
                    {new Date(document.uploadedAt).toLocaleString('tr-TR', {
                      day: '2-digit',
                      month: 'short',
                      year: 'numeric',
                      hour: '2-digit',
                      minute: '2-digit',
                    })}
                  </td>
                  <td className="px-4 py-3">
                    <div className="flex items-center justify-end gap-2">
                      <button className="inline-flex items-center gap-1 rounded-full border border-slate-200 px-3 py-1 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                        <span className="material-symbols-outlined text-base">download</span>
                        İndir
                      </button>
                      <button
                        onClick={() => setSelected(document)}
                        className="inline-flex items-center gap-1 rounded-full border border-slate-200 px-3 py-1 text-xs font-semibold text-slate-600 hover:bg-slate-50"
                      >
                        <span className="material-symbols-outlined text-base">layers</span>
                        Versiyonlar
                      </button>
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        ) : (
          <p className="p-6 text-center text-sm text-slate-500">Henüz doküman yüklenmemiş.</p>
        )}
      </div>

      {selected && (
        <div className="fixed inset-0 z-40 flex items-center justify-center bg-black/50 px-4">
          <div className="w-full max-w-md rounded-3xl bg-white p-6 shadow-lg">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-xs uppercase tracking-wide text-slate-400">Versiyonlar</p>
                <h3 className="text-xl font-semibold text-slate-900">{selected.name}</h3>
              </div>
              <button
                onClick={() => setSelected(null)}
                className="rounded-full bg-slate-100 p-2 text-slate-500 hover:bg-slate-200"
              >
                <span className="material-symbols-outlined">close</span>
              </button>
            </div>
            <ul className="mt-4 space-y-3 text-sm text-slate-600">
              {selected.versions.map((version) => (
                <li
                  key={version.id}
                  className="flex items-start justify-between rounded-2xl border border-slate-100 bg-slate-50 p-3"
                >
                  <div>
                    <p className="font-semibold text-slate-900">{version.version}</p>
                    <p className="text-xs text-slate-400">{version.uploadedBy}</p>
                  </div>
                  <span className="text-xs text-slate-500">
                    {new Date(version.uploadedAt).toLocaleString('tr-TR', {
                      day: '2-digit',
                      month: 'short',
                      year: 'numeric',
                      hour: '2-digit',
                      minute: '2-digit',
                    })}
                  </span>
                </li>
              ))}
            </ul>
          </div>
        </div>
      )}
    </>
  );
};
