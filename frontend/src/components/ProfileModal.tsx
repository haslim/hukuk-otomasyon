import { useState } from 'react';
import { AuthUser, useAuth } from '../context/AuthContext';
import { ProfileApi } from '../api/modules/profile';

interface Props {
  open: boolean;
  onClose: () => void;
}

export const ProfileModal = ({ open, onClose }: Props) => {
  const { user, setUser } = useAuth();
  const [form, setForm] = useState<AuthUser>(() => ({
    id: user?.id ?? '',
    name: user?.name ?? '',
    email: user?.email ?? '',
    title: user?.title ?? '',
    avatarUrl: user?.avatarUrl,
  }));
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState('');

  if (!open) return null;

  const handleChange =
    (field: keyof AuthUser) => (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
      setForm((prev) => ({ ...prev, [field]: e.target.value }));
    };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setSaving(true);
    setError('');
    try {
      const updated = await ProfileApi.updateProfile({
        name: form.name,
        title: form.title,
        avatarUrl: form.avatarUrl,
      });
      setUser(updated);
      onClose();
    } catch (err: any) {
      const message = err?.response?.data?.message ?? 'Profil güncellenirken bir hata oluştu.';
      setError(message);
    } finally {
      setSaving(false);
    }
  };

  return (
    <div className="fixed inset-0 z-40 flex items-center justify-center bg-black/40 px-4">
      <div className="w-full max-w-lg rounded-2xl bg-white p-6 shadow-lg">
        <div className="flex items-center justify-between mb-4">
          <h2 className="text-xl font-semibold text-slate-900">Profil Bilgileri</h2>
          <button type="button" onClick={onClose} className="rounded-full p-2 text-slate-500 hover:bg-slate-100">
            <span className="material-symbols-outlined">close</span>
          </button>
        </div>
        {error && (
          <div className="mb-4 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
            {error}
          </div>
        )}
        <form className="space-y-4" onSubmit={handleSubmit}>
          <div className="flex items-center gap-4">
            <div
              className="bg-center bg-no-repeat bg-cover rounded-full size-16"
              style={{
                backgroundImage:
                  `url("${form.avatarUrl || 'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?w=200&auto=format&fit=crop&q=80'}")`,
              }}
            />
            <div className="flex-1 space-y-2">
              <label className="block text-sm font-medium text-gray-700">
                Fotoğraf URL
                <input
                  className="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#2463eb]"
                  value={form.avatarUrl ?? ''}
                  onChange={handleChange('avatarUrl')}
                  placeholder="Profil fotoğrafı için resim linki"
                />
              </label>
            </div>
          </div>
          <label className="block text-sm font-medium text-gray-700">
            Ad Soyad
            <input
              className="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#2463eb]"
              value={form.name}
              onChange={handleChange('name')}
              required
            />
          </label>
          <label className="block text-sm font-medium text-gray-700">
            Ünvan
            <input
              className="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#2463eb]"
              value={form.title ?? ''}
              onChange={handleChange('title')}
              placeholder="Avukat, Stajyer, vb."
            />
          </label>
          <label className="block text-sm font-medium text-gray-700">
            E-posta
            <input
              className="mt-1 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-500"
              value={form.email}
              disabled
            />
          </label>
          <div className="mt-4 flex justify-end gap-3">
            <button
              type="button"
              onClick={onClose}
              className="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
            >
              İptal
            </button>
            <button
              type="submit"
              disabled={saving}
              className="rounded-lg bg-[#2463eb] px-5 py-2 text-sm font-semibold text-white hover:bg-[#1d4fd8] disabled:opacity-60"
            >
              {saving ? 'Kaydediliyor...' : 'Kaydet'}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
};

