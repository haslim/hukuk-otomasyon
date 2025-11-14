import { useState, ReactNode, FormEvent } from 'react';
import { apiClient } from '../api/client';
import { useAuth } from '../context/AuthContext';

interface Props {
  children: ReactNode;
}

export const LoginGate = ({ children }: Props) => {
  const { token, setToken } = useAuth();
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [remember, setRemember] = useState(false);
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);

  const onSubmit = async (event: FormEvent) => {
    event.preventDefault();
    setLoading(true);
    setError('');
    try {
      const { data } = await apiClient.post('/auth/login', { email, password, remember });
      setToken(data.token);
    } catch (err: any) {
      const message = err?.response?.data?.message ?? 'Girdiğiniz e-posta veya şifre hatalı. Lütfen kontrol ediniz.';
      setError(message);
    } finally {
      setLoading(false);
    }
  };

  if (!token) {
    return (
      <div className="min-h-screen bg-[#f6f6f8] text-gray-800">
        <div className="mx-auto flex min-h-screen max-w-6xl flex-col justify-center px-4 py-12 lg:px-8">
          <div className="grid grid-cols-1 gap-10 md:grid-cols-2 md:items-center">
            <div className="space-y-4 px-2 text-center md:text-left">
              <div className="flex items-center justify-center gap-3 text-[#2463eb] md:justify-start">
                <span className="material-symbols-outlined text-4xl">gavel</span>
                <span className="text-3xl font-bold text-gray-900">BGAofis</span>
              </div>
              <h1 className="text-3xl font-bold text-gray-900 leading-tight">
                Hukuk Yönetiminde Dijital Çözümünüz
              </h1>
              <p className="text-base text-gray-600">
                Müvekkil yönetimi, dava takibi ve doküman arşivleme gibi tüm süreçlerinizi tek bir platformdan yönetin.
              </p>
            </div>
            <div className="mx-auto w-full max-w-md rounded-2xl border border-gray-100 bg-white p-8 shadow-lg">
              <h2 className="mb-6 text-2xl font-bold text-gray-900">Sisteme Giriş</h2>
              {error && (
                <div className="mb-6 flex items-center gap-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                  <span className="material-symbols-outlined text-xl">error</span>
                  <p>{error}</p>
                </div>
              )}
              <form className="space-y-5" onSubmit={onSubmit}>
                <label className="flex flex-col gap-2">
                  <span className="text-sm font-medium text-gray-700">E-posta Adresi</span>
                  <div className="flex items-center rounded-lg border border-gray-300 bg-[#f6f6f8]">
                    <span className="material-symbols-outlined px-3 text-gray-500">mail</span>
                    <input
                      className="h-12 flex-1 rounded-r-lg bg-transparent px-3 text-base text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#2463eb]"
                      placeholder="e-posta@adresiniz.com"
                      type="email"
                      value={email}
                      onChange={(e) => setEmail(e.target.value)}
                      required
                    />
                  </div>
                </label>
                <label className="flex flex-col gap-2">
                  <span className="text-sm font-medium text-gray-700">Şifre</span>
                  <div className="flex items-center rounded-lg border border-gray-300 bg-[#f6f6f8]">
                    <span className="material-symbols-outlined px-3 text-gray-500">lock</span>
                    <input
                      className="h-12 flex-1 rounded-r-lg bg-transparent px-3 text-base text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#2463eb]"
                      placeholder="Şifrenizi giriniz"
                      type="password"
                      value={password}
                      onChange={(e) => setPassword(e.target.value)}
                      required
                    />
                  </div>
                </label>
                <div className="flex items-center justify-between text-sm text-gray-600">
                  <label className="inline-flex cursor-pointer select-none items-center gap-2">
                    <input
                      type="checkbox"
                      className="h-4 w-4 rounded border-gray-300 text-[#2463eb] focus:ring-[#2463eb]"
                      checked={remember}
                      onChange={(e) => setRemember(e.target.checked)}
                    />
                    Beni Hatırla
                  </label>
                  <a className="font-medium text-[#2463eb] hover:underline" href="#">
                    Şifremi unuttum?
                  </a>
                </div>
                <button
                  type="submit"
                  disabled={loading}
                  className="flex w-full items-center justify-center rounded-lg bg-[#2463eb] py-3 text-base font-semibold text-white transition hover:bg-[#1d4fd8] disabled:opacity-60"
                >
                  {loading ? 'Giriş yapılıyor...' : 'Giriş Yap'}
                </button>
              </form>
            </div>
          </div>
        </div>
      </div>
    );
  }

  return <>{children}</>;
};
