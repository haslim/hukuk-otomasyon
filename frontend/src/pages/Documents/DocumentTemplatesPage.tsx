import { useState } from 'react';
import { useDocumentTemplates } from '../../hooks/useDocumentTemplates';

export const DocumentTemplatesPage = () => {
  const { templates, addTemplate, updateTemplate, removeTemplate } = useDocumentTemplates();
  const [newLabel, setNewLabel] = useState('');

  const handleAdd = () => {
    if (!newLabel.trim()) return;
    addTemplate(newLabel);
    setNewLabel('');
  };

  return (
    <section className="space-y-6 max-w-3xl">
      <header className="space-y-1">
        <h1 className="text-2xl font-semibold text-[#0f172a]">Belge Şablonları</h1>
        <p className="text-sm text-[#64748b]">
          Arabuluculuk dosyalarında &quot;Şablondan Belge Oluştur&quot; menüsünde görünen
          belge şablonlarını buradan ekleyip düzenleyebilirsiniz.
        </p>
      </header>

      <div className="rounded-2xl border border-[#e2e8f0] bg-white p-4 shadow-sm space-y-4">
        <div className="flex gap-2">
          <input
            type="text"
            placeholder="Yeni şablon adı (örn. Duruşma Tutanağı)"
            value={newLabel}
            onChange={(e) => setNewLabel(e.target.value)}
            className="flex-1 rounded-lg border border-[#e2e8f0] px-3 py-2 text-sm focus:border-[#2463eb] focus:outline-none"
          />
          <button
            type="button"
            onClick={handleAdd}
            className="rounded-lg bg-[#2463eb] px-4 py-2 text-sm font-semibold text-white hover:bg-[#1d4fd8]"
          >
            Ekle
          </button>
        </div>

        <div className="divide-y divide-[#e5e7eb]">
          {templates.map((tpl) => (
            <div key={tpl.id} className="flex items-center justify-between py-2 gap-3">
              <input
                type="text"
                defaultValue={tpl.label}
                onBlur={(e) => updateTemplate(tpl.id, e.target.value)}
                className="flex-1 rounded-lg border border-transparent px-2 py-1 text-sm focus:border-[#2463eb] focus:outline-none"
              />
              <button
                type="button"
                onClick={() => removeTemplate(tpl.id)}
                className="rounded-lg border border-[#e2e8f0] px-3 py-1 text-xs font-medium text-[#ef4444] hover:bg-red-50"
              >
                Sil
              </button>
            </div>
          ))}

          {templates.length === 0 && (
            <p className="py-4 text-sm text-[#64748b]">
              Henüz şablon yok. Yukarıdan yeni bir şablon ekleyebilirsiniz.
            </p>
          )}
        </div>
      </div>
    </section>
  );
};

