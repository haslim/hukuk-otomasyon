import { useState } from 'react';
import { SearchApi } from '../../api/modules/search';

export const SearchPage = () => {
  const [term, setTerm] = useState('');
  const [result, setResult] = useState<any>();

  const onSearch = async (event: React.FormEvent) => {
    event.preventDefault();
    const data = await SearchApi.global(term);
    setResult(data);
  };

  const renderList = (items: any[] = [], title: string) => (
    <article className="rounded bg-white p-4 shadow">
      <h3 className="mb-2 font-semibold">{title}</h3>
      <ul className="space-y-1 text-sm text-slate-600">
        {items.map((item) => (
          <li key={item.id}>{item.name ?? item.title}</li>
        ))}
      </ul>
    </article>
  );

  return (
    <section className="space-y-6">
      <header>
        <h2 className="text-2xl font-semibold">Gelişmiş Arama</h2>
        <p className="text-slate-600">TCKN/VKN, dosya no, doküman içerikleri dahil tam metin.</p>
      </header>

      <form onSubmit={onSearch} className="flex items-center gap-3 rounded bg-white p-4 shadow">
        <input className="input flex-1" placeholder="Ara" value={term} onChange={(e) => setTerm(e.target.value)} />
        <button className="rounded bg-indigo-600 px-4 py-2 text-white" type="submit">
          Tara
        </button>
      </form>

      {result && (
        <div className="grid grid-cols-3 gap-4">
          {renderList(result.clients, 'Müvekkiller')}
          {renderList(result.cases, 'Dosyalar')}
          {renderList(result.documents, 'Dokümanlar')}
        </div>
      )}
    </section>
  );
};
