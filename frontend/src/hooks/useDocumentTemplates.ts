import { useEffect, useState } from 'react';

export interface DocumentTemplate {
  id: string;
  key: string;
  label: string;
}

const STORAGE_KEY = 'document_templates';

const DEFAULT_TEMPLATES: DocumentTemplate[] = [
  { id: 'basvuru', key: 'basvuru', label: 'Başvuru Formu' },
  { id: 'ilk-toplanti', key: 'ilk', label: 'İlk Toplantı Tutanağı' },
  { id: 'son-tutanak', key: 'son', label: 'Son Tutanak' },
  { id: 'anlasma-belgesi', key: 'anlasma', label: 'Anlaşma Belgesi' },
];

export const useDocumentTemplates = () => {
  const [templates, setTemplates] = useState<DocumentTemplate[]>(() => {
    if (typeof window === 'undefined') {
      return DEFAULT_TEMPLATES;
    }

    try {
      const raw = window.localStorage.getItem(STORAGE_KEY);
      if (!raw) return DEFAULT_TEMPLATES;

      const parsed = JSON.parse(raw) as DocumentTemplate[];
      if (!Array.isArray(parsed) || parsed.length === 0) {
        return DEFAULT_TEMPLATES;
      }

      return parsed;
    } catch {
      return DEFAULT_TEMPLATES;
    }
  });

  useEffect(() => {
    try {
      window.localStorage.setItem(STORAGE_KEY, JSON.stringify(templates));
    } catch {
      // ignore storage errors
    }
  }, [templates]);

  const addTemplate = (label: string) => {
    const trimmed = label.trim();
    if (!trimmed) return;

    const key = trimmed.toLowerCase().replace(/\s+/g, '-');
    const id = `${key}-${Date.now()}`;

    setTemplates((prev) => [...prev, { id, key, label: trimmed }]);
  };

  const updateTemplate = (id: string, label: string) => {
    const trimmed = label.trim();
    if (!trimmed) return;

    setTemplates((prev) =>
      prev.map((tpl) => (tpl.id === id ? { ...tpl, label: trimmed } : tpl)),
    );
  };

  const removeTemplate = (id: string) => {
    setTemplates((prev) => prev.filter((tpl) => tpl.id !== id));
  };

  return {
    templates,
    addTemplate,
    updateTemplate,
    removeTemplate,
  };
};

