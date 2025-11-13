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

  const onSubmit = async (event: FormEvent) => {
    event.preventDefault();
    const { data } = await apiClient.post('/auth/login', { email, password });
    setToken(data.token);
  };

  if (!token) {
    return (
      <form onSubmit={onSubmit} className="mx-auto mt-24 max-w-sm space-y-4 rounded bg-white p-6 shadow">
        <h2 className="text-xl font-semibold">BGAofis Giriş</h2>
        <input className="input w-full" placeholder="E-posta" value={email} onChange={(e) => setEmail(e.target.value)} />
        <input
          className="input w-full"
          type="password"
          placeholder="Şifre"
          value={password}
          onChange={(e) => setPassword(e.target.value)}
        />
        <button className="w-full rounded bg-indigo-600 px-4 py-2 text-white" type="submit">
          Giriş Yap
        </button>
      </form>
    );
  }

  return <>{children}</>;
};
