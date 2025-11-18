import { createContext, ReactNode, useCallback, useContext, useState } from 'react';

type NotificationType = 'success' | 'error' | 'info';

interface Notification {
  id: string;
  message: string;
  type: NotificationType;
}

interface NotificationContextValue {
  notify: (message: string, type?: NotificationType) => void;
}

const NotificationContext = createContext<NotificationContextValue | undefined>(undefined);
const AUTO_DISMISS_MS = 4500;

const buildNotificationId = () =>
  typeof globalThis.crypto !== 'undefined' && 'randomUUID' in globalThis.crypto
    ? globalThis.crypto.randomUUID()
    : `${Date.now()}-${Math.random().toString(36).slice(2, 8)}`;

const notificationStyles: Record<NotificationType, string> = {
  success: 'bg-emerald-500 text-white',
  error: 'bg-rose-500 text-white',
  info: 'bg-sky-500 text-white',
};

export const NotificationProvider = ({ children }: { children: ReactNode }) => {
  const [notifications, setNotifications] = useState<Notification[]>([]);

  const notify = useCallback((message: string, type: NotificationType = 'info') => {
    const id = buildNotificationId();
    setNotifications((previous) => [...previous, { id, message, type }]);
    setTimeout(() => {
      setNotifications((previous) => previous.filter((note) => note.id !== id));
    }, AUTO_DISMISS_MS);
  }, []);

  return (
    <NotificationContext.Provider value={{ notify }}>
      {children}
      <div className="pointer-events-none fixed right-4 top-4 z-[9999] flex flex-col gap-3">
        {notifications.map((notification) => (
          <div
            key={notification.id}
            role="status"
            className={`pointer-events-auto max-w-xs rounded-xl px-4 py-2 text-sm font-medium shadow-lg transition-all duration-200 ${
              notificationStyles[notification.type]
            }`}
          >
            {notification.message}
          </div>
        ))}
      </div>
    </NotificationContext.Provider>
  );
};

export const useNotification = () => {
  const context = useContext(NotificationContext);
  if (!context) {
    throw new Error('useNotification must be used within a NotificationProvider');
  }
  return context;
};
