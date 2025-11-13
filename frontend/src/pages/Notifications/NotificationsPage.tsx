import { NotificationApi } from '../../api/modules/notifications';
import { useAsyncData } from '../../hooks/useAsyncData';

export const NotificationsPage = () => {
  const { data, isLoading } = useAsyncData(['notifications'], NotificationApi.list);

  return (
    <section className="space-y-4">
      <header>
        <h2 className="text-2xl font-semibold">Bildirimler & Hatırlatmalar</h2>
        <p className="text-slate-600">Cron tabanlı e-posta sistemi ve pending_notifications kuyruğu.</p>
      </header>

      {isLoading && <p>Yükleniyor...</p>}

      <div className="space-y-3">
        {data?.map((notification: any) => (
          <article key={notification.id} className="rounded bg-white p-4 shadow">
            <div className="flex items-center justify-between">
              <div>
                <h3 className="font-medium">{notification.subject}</h3>
                <p className="text-sm text-slate-500">{notification.status}</p>
              </div>
              <code className="rounded bg-slate-100 px-2 py-1 text-xs">
                {notification.channels?.join(', ')}
              </code>
            </div>
          </article>
        ))}
      </div>
    </section>
  );
};
