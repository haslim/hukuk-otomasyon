import { useState } from 'react';
import { useNotifications, Notification } from '../hooks/useNotifications';

export const NotificationDropdown = () => {
  const [isOpen, setIsOpen] = useState(false);
  const { notifications, unreadCount, markAsRead, markAllAsRead } = useNotifications();

  const handleNotificationClick = (notification: Notification) => {
    if (!notification.read) {
      markAsRead(notification.id);
    }
  };

  const handleMarkAllAsRead = () => {
    markAllAsRead();
  };

  const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    const now = new Date();
    const diffInHours = Math.floor((now.getTime() - date.getTime()) / (1000 * 60 * 60));
    
    if (diffInHours < 1) {
      const diffInMinutes = Math.floor((now.getTime() - date.getTime()) / (1000 * 60));
      return `${diffInMinutes} dakika önce`;
    } else if (diffInHours < 24) {
      return `${diffInHours} saat önce`;
    } else {
      const diffInDays = Math.floor(diffInHours / 24);
      return `${diffInDays} gün önce`;
    }
  };

  const getNotificationIcon = (type: string) => {
    switch (type) {
      case 'success':
        return 'check_circle';
      case 'warning':
        return 'warning';
      case 'error':
        return 'error';
      default:
        return 'info';
    }
  };

  const getNotificationColor = (type: string) => {
    switch (type) {
      case 'success':
        return 'text-green-500';
      case 'warning':
        return 'text-yellow-500';
      case 'error':
        return 'text-red-500';
      default:
        return 'text-blue-500';
    }
  };

  return (
    <div className="relative">
      <button
        type="button"
        onClick={() => setIsOpen(!isOpen)}
        className="relative flex items-center justify-center rounded-lg h-10 w-10 border border-[#E2E8F0] hover:bg-gray-100 transition-colors"
      >
        <span className="material-symbols-outlined text-[#1A202C]">notifications</span>
        {unreadCount > 0 && (
          <span className="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-white text-[10px] font-bold">
            {unreadCount > 99 ? '99+' : unreadCount}
          </span>
        )}
      </button>

      {isOpen && (
        <>
          <div
            className="fixed inset-0 z-10"
            onClick={() => setIsOpen(false)}
          />
          <div className="absolute right-0 mt-2 w-80 rounded-lg border border-[#E2E8F0] bg-white shadow-lg z-20 max-h-96 overflow-hidden">
            <div className="flex items-center justify-between p-4 border-b border-[#E2E8F0]">
              <h3 className="font-semibold text-[#1A202C]">Bildirimler</h3>
              {unreadCount > 0 && (
                <button
                  onClick={handleMarkAllAsRead}
                  className="text-sm text-[#2463eb] hover:text-[#1e40af] transition-colors"
                >
                  Tümünü okundu işaretle
                </button>
              )}
            </div>

            <div className="overflow-y-auto max-h-64">
              {notifications.length === 0 ? (
                <div className="p-8 text-center text-[#A0AEC0]">
                  <span className="material-symbols-outlined text-4xl mb-2 block">notifications_off</span>
                  <p>Bildirim bulunmuyor</p>
                </div>
              ) : (
                notifications.map((notification) => (
                  <div
                    key={notification.id}
                    onClick={() => handleNotificationClick(notification)}
                    className={`p-4 border-b border-[#E2E8F0] hover:bg-gray-50 cursor-pointer transition-colors ${
                      !notification.read ? 'bg-blue-50' : ''
                    }`}
                  >
                    <div className="flex items-start gap-3">
                      <span className={`material-symbols-outlined ${getNotificationColor(notification.type)}`}>
                        {getNotificationIcon(notification.type)}
                      </span>
                      <div className="flex-1 min-w-0">
                        {notification.title && (
                          <p className="font-medium text-[#1A202C] text-sm">
                            {notification.title}
                          </p>
                        )}
                        <p className="text-sm text-[#4A5568] break-words">
                          {notification.message}
                        </p>
                        <p className="text-xs text-[#A0AEC0] mt-1">
                          {formatDate(notification.createdAt)}
                        </p>
                      </div>
                      {!notification.read && (
                        <div className="w-2 h-2 bg-blue-500 rounded-full mt-2 flex-shrink-0" />
                      )}
                    </div>
                  </div>
                ))
              )}
            </div>

            {notifications.length > 0 && (
              <div className="p-3 border-t border-[#E2E8F0]">
                <button
                  onClick={() => {
                    window.location.href = '/notifications';
                  }}
                  className="w-full text-center text-sm text-[#2463eb] hover:text-[#1e40af] transition-colors"
                >
                  Tüm bildirimleri görüntüle
                </button>
              </div>
            )}
          </div>
        </>
      )}
    </div>
  );
};
