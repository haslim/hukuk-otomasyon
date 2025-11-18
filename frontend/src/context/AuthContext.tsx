import { createContext, useContext, useState, ReactNode } from 'react';

export const DEFAULT_AVATAR_URL =
  'https://upload.wikimedia.org/wikipedia/commons/b/b4/Flag_of_Turkey.svg';

export interface AuthUser {
  id: string;
  name: string;
  email: string;
  title?: string;
  avatarUrl?: string;
  roles?: string[];
}

interface AuthState {
  token: string | null;
  user: AuthUser | null;
  setToken: (token: string | null) => void;
  setUser: (user: AuthUser | null) => void;
  logout: () => void;
}

const AuthContext = createContext<AuthState | undefined>(undefined);

export const AuthProvider = ({ children }: { children: ReactNode }) => {
  const [token, setTokenState] = useState<string | null>(() => localStorage.getItem('token'));
  const [user, setUserState] = useState<AuthUser | null>(() => {
    const raw = localStorage.getItem('user');
    if (!raw) return null;
    try {
      return JSON.parse(raw) as AuthUser;
    } catch {
      return null;
    }
  });

  const setToken = (value: string | null) => {
    setTokenState(value);
    if (value) {
      localStorage.setItem('token', value);
    } else {
      localStorage.removeItem('token');
    }
  };

  const setUser = (value: AuthUser | null) => {
    setUserState(value);
    if (value) {
      localStorage.setItem('user', JSON.stringify(value));
    } else {
      localStorage.removeItem('user');
    }
  };

  const logout = () => {
    setToken(null);
    setUser(null);
  };

  return (
    <AuthContext.Provider value={{ token, user, setToken, setUser, logout }}>
      {children}
    </AuthContext.Provider>
  );
};

export const useAuth = () => {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error('useAuth must be used within AuthProvider');
  }
  return context;
};
