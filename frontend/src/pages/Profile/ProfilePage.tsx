import { FormEvent, useMemo, useState } from 'react';
import { useAuth } from '../../context/AuthContext';

const DEFAULT_AVATAR =
  'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?w=400&auto=format&fit=crop&q=80';

export const ProfilePage = () => {
  const { user } = useAuth();
  const [currentPassword, setCurrentPassword] = useState('');
  const [newPassword, setNewPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');
  const [feedback, setFeedback] = useState<string | null>(null);
  const [error, setError] = useState<string | null>(null);

  const roleLabel = useMemo(() => {
    if (user?.roles && user.roles.length > 0) {
      return user.roles.join(', ');
    }
    if (user?.title) {
      return user.title;
    }
    return 'Avukat';
  }, [user]);

  const handleSubmit = (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    setFeedback(null);
    setError(null);

    if (!currentPassword || !newPassword || !confirmPassword) {
      setError('Lütfen tüm alanları doldurun.');
      return;
    }

    if (newPassword !== confirmPassword) {
      setError('Yeni şifre ile tekrar şifre eşleşmiyor.');
      return;
    }

    setFeedback('Şifreniz başarıyla güncellendi.');
    setCurrentPassword('');
    setNewPassword('');
    setConfirmPassword('');
  };

  const infoRows = [
    { label: 'Ad Soyad', value: user?.name ?? 'Av. Ad Soyad' },
    { label: 'E-posta', value: user?.email ?? 'ornek@bgaofis.com' },
    { label: 'Rol(ler)', value: roleLabel },
  ];

  return (
    <div className="space-y-8">
      <header className="flex flex-col gap-2">
        <p className="text-sm font-semibold uppercase tracking-[0.2em] text-[#4d6599]">Hesabım</p>
        <h1 className="text-3xl font-black text-[#0e121b]">Profilim</h1>
        <p className="max-w-2xl text-sm text-[#6b7280]">
          Profil bilgilerini güncelle, şifre değiştir ve hesabın genel görünümünü incele.
        </p>
      </header>

      <section className="overflow-hidden rounded-[32px] border border-[#dfe3ee] bg-gradient-to-r from-white to-[#f2f6ff] p-8 shadow-sm">
        <div className="flex flex-col gap-6 md:flex-row md:items-center">
          <div className="flex items-center gap-6">
            <div className="h-24 w-24 overflow-hidden rounded-full border border-white/70 shadow-sm">
              <img
                src={user?.avatarUrl ?? DEFAULT_AVATAR}
                alt={user?.name ?? 'Profil resmi'}
                className="h-full w-full object-cover"
              />
            </div>
            <div>
              <p className="text-sm uppercase tracking-[0.4em] text-[#4d6599]">BGAofis</p>
              <p className="text-2xl font-bold text-[#0f172a]">{user?.name ?? 'Av. İsimsiz Kullanıcı'}</p>
              <p className="text-sm font-medium text-[#6b7280]">{user?.email ?? 'ornek@bgaofis.com'}</p>
            </div>
          </div>
          <div className="flex-1 rounded-2xl border border-dashed border-[#cdd5ff] bg-white/80 p-6 text-sm text-[#475569] shadow-sm">
            <p className="text-base font-semibold text-[#1d4fd8]">Profesyonel Özet</p>
            <p className="mt-2 leading-relaxed text-[#475569]">
              Hukuk otomasyon panelini yakından takip ederek müvekkillerini, dosyalarını ve takvimini tek
              çatı altında yönetiyorsun. Profil bilgilerinden detaylı iletişim bilgilerine kadar her şey burada.
            </p>
          </div>
        </div>
      </section>

      <section className="rounded-[32px] border border-[#e2e8f0] bg-white p-6 shadow-sm">
        <div className="flex items-center justify-between gap-4 border-b border-[#f1f5f9] pb-4">
          <div>
            <h2 className="text-lg font-bold text-[#0e121b]">Kişisel Bilgiler</h2>
            <p className="text-sm text-[#64748b]">İletişim ve rol bilgilerin</p>
          </div>
          <button
            type="button"
            className="inline-flex items-center gap-1 rounded-lg border border-[#e2e8f0] px-3 py-2 text-xs font-semibold text-[#2463eb] hover:bg-[#2463eb]/10"
          >
            <span className="material-symbols-outlined text-base">download</span>
            PDF İndir
          </button>
        </div>
        <dl className="mt-6 grid gap-4 sm:grid-cols-[150px_minmax(0,1fr)]">
          {infoRows.map((row) => (
            <div key={row.label} className="flex flex-col gap-1 border-b border-[#f1f5f9] pb-4 last:border-b-0 last:pb-0">
              <dt className="text-xs font-medium uppercase tracking-[0.3em] text-[#94a3b8]">{row.label}</dt>
              <dd className="text-sm font-semibold text-[#0f172a]">{row.value}</dd>
            </div>
          ))}
        </dl>
      </section>

      <section className="rounded-[32px] border border-[#e2e8f0] bg-white p-6 shadow-sm">
        <div className="mb-4">
          <h2 className="text-lg font-bold text-[#0e121b]">Şifre Değiştir</h2>
          <p className="text-sm text-[#64748b]">Kişisel güvenlik ayarlarını güncelle.</p>
        </div>
        <form className="space-y-6" onSubmit={handleSubmit}>
          <div>
            <label className="text-sm font-medium text-[#475569]" htmlFor="current-password">
              Eski Şifre
            </label>
            <div className="relative mt-2">
              <input
                id="current-password"
                type="password"
                value={currentPassword}
                onChange={(event) => setCurrentPassword(event.target.value)}
                className="w-full rounded-xl border border-[#e2e8f0] bg-[#f8fafc] px-4 py-3 text-sm focus:border-[#2463eb] focus:outline-none"
              />
              <button
                type="button"
                className="absolute inset-y-0 right-0 flex items-center pr-4 text-[#94a3b8]"
                aria-label="Show current password"
              >
                <span className="material-symbols-outlined">visibility_off</span>
              </button>
            </div>
          </div>
          <div>
            <label className="text-sm font-medium text-[#475569]" htmlFor="new-password">
              Yeni Şifre
            </label>
            <div className="relative mt-2">
              <input
                id="new-password"
                type="password"
                value={newPassword}
                onChange={(event) => setNewPassword(event.target.value)}
                className="w-full rounded-xl border border-[#e2e8f0] bg-[#f8fafc] px-4 py-3 text-sm focus:border-[#2463eb] focus:outline-none"
              />
              <button
                type="button"
                className="absolute inset-y-0 right-0 flex items-center pr-4 text-[#94a3b8]"
                aria-label="Show new password"
              >
                <span className="material-symbols-outlined">visibility_off</span>
              </button>
            </div>
          </div>
          <div>
            <label className="text-sm font-medium text-[#475569]" htmlFor="confirm-password">
              Yeni Şifre Tekrar
            </label>
            <div className="relative mt-2">
              <input
                id="confirm-password"
                type="password"
                value={confirmPassword}
                onChange={(event) => setConfirmPassword(event.target.value)}
                className="w-full rounded-xl border border-[#e2e8f0] bg-[#f8fafc] px-4 py-3 text-sm focus:border-[#2463eb] focus:outline-none"
              />
              <button
                type="button"
                className="absolute inset-y-0 right-0 flex items-center pr-4 text-[#94a3b8]"
                aria-label="Show confirm password"
              >
                <span className="material-symbols-outlined">visibility_off</span>
              </button>
            </div>
          </div>
          {error && (
            <p className="text-sm font-medium text-red-600 dark:text-red-500">{error}</p>
          )}
          {feedback && (
            <p className="text-sm font-medium text-green-600 dark:text-green-400">{feedback}</p>
          )}
          <div className="flex justify-end">
            <button
              type="submit"
              className="rounded-xl bg-[#2463eb] px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#1b4dda]"
            >
              Şifreyi Güncelle
            </button>
          </div>
        </form>
      </section>
    </div>
  );
};
