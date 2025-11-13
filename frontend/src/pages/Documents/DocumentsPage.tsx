import { useState } from 'react';
import { DocumentApi } from '../../api/modules/documents';

export const DocumentsPage = () => {
  const [term, setTerm] = useState('');
  const [results, setResults] = useState<any[]>([]);

  const onSearch = async (event: React.FormEvent) => {
    event.preventDefault();
    const data = await DocumentApi.search(term);
    setResults(data ?? []);
  };

  return (
    <section className="space-y-6">
      <header>
        <h2 className="text-2xl font-semibold">Doküman Yönetimi</h2>
        <p className="text-slate-600">Versiyonlama ve tam metin arama</p>
      </header>
      <form onSubmit={onSearch} className="flex items-center gap-3 rounded bg-white p-4 shadow">
        <input className="input flex-1" placeholder="Kelime arayın" value={term} onChange={(e) => setTerm(e.target.value)} />
        <button className="rounded bg-indigo-600 px-4 py-2 text-white" type="submit">
          Ara
        </button>
      </form>

      <div className="space-y-3">
        {results.map((doc) => (
          <article key={doc.id} className="rounded bg-white p-4 shadow">
            <h3 className="text-lg font-semibold">{doc.title}</h3>
            <p className="text-sm text-slate-500">{doc.type}</p>
          </article>
        ))}
      </div>
    </section>
  );
};
