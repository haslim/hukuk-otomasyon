import { useState, useEffect } from 'react';
import { SettingsApi, SettingsData } from '../api/modules/users';
import { useAsyncData } from '../hooks/useAsyncData';
import { UsersSectionLayout } from './Users/UsersSectionLayout';
import { useNotification } from '../context/NotificationContext';

export const SettingsPage = () => {
  const { data: settingsData, isLoading, refetch } = useAsyncData(['settings'], SettingsApi.getSettings);
  const [settings, setSettings] = useState<SettingsData>({
    notifications: {
      emailNotifications: true,
      pushNotifications: true,
      caseUpdates: true,
      taskReminders: true,
    },
    appearance: {
      theme: 'light',
      language: 'tr',
      timezone: 'Europe/Istanbul',
    },
    privacy: {
      showProfileToOthers: true,
      showOnlineStatus: true,
    },
  });
  const [hasChanges, setHasChanges] = useState(false);
  const { notify } = useNotification();

  useEffect(() => {
    console.log('SettingsPage - settingsData changed:', settingsData);
    if (settingsData && typeof settingsData === 'object') {
      // Ensure all nested properties exist before setting
      const safeSettings: SettingsData = {
        notifications: {
          emailNotifications: settingsData.notifications?.emailNotifications ?? true,
          pushNotifications: settingsData.notifications?.pushNotifications ?? true,
          caseUpdates: settingsData.notifications?.caseUpdates ?? true,
          taskReminders: settingsData.notifications?.taskReminders ?? true,
        },
        appearance: {
          theme: settingsData.appearance?.theme ?? 'light',
          language: settingsData.appearance?.language ?? 'tr',
          timezone: settingsData.appearance?.timezone ?? 'Europe/Istanbul',
        },
        privacy: {
          showProfileToOthers: settingsData.privacy?.showProfileToOthers ?? true,
          showOnlineStatus: settingsData.privacy?.showOnlineStatus ?? true,
        },
      };
      console.log('SettingsPage - safeSettings created:', safeSettings);
      setSettings(safeSettings);
      setHasChanges(false);
    }
  }, [settingsData]);

  const handleSave = async () => {
    try {
      await SettingsApi.updateSettings(settings);
      await refetch();
      setHasChanges(false);
      notify('Ayarlar başarıyla kaydedildi', 'success');
    } catch (error) {
      const message = error instanceof Error ? error.message : 'Ayarlar kaydedilirken hata oluştu.';
      notify(message, 'error');
    }
  };

  const handleCancel = () => {
    if (settingsData && typeof settingsData === 'object') {
      // Apply the same safe transformation as in useEffect
      const safeSettings: SettingsData = {
        notifications: {
          emailNotifications: settingsData.notifications?.emailNotifications ?? true,
          pushNotifications: settingsData.notifications?.pushNotifications ?? true,
          caseUpdates: settingsData.notifications?.caseUpdates ?? true,
          taskReminders: settingsData.notifications?.taskReminders ?? true,
        },
        appearance: {
          theme: settingsData.appearance?.theme ?? 'light',
          language: settingsData.appearance?.language ?? 'tr',
          timezone: settingsData.appearance?.timezone ?? 'Europe/Istanbul',
        },
        privacy: {
          showProfileToOthers: settingsData.privacy?.showProfileToOthers ?? true,
          showOnlineStatus: settingsData.privacy?.showOnlineStatus ?? true,
        },
      };
      setSettings(safeSettings);
      setHasChanges(false);
    }
  };

  const updateNotificationSetting = (key: keyof SettingsData['notifications'], value: boolean) => {
    setSettings(prev => {
      if (!prev) return prev;
      return {
        ...prev,
        notifications: {
          ...prev.notifications,
          [key]: value,
        },
      };
    });
    setHasChanges(true);
  };

  const updateAppearanceSetting = (key: keyof SettingsData['appearance'], value: string) => {
    setSettings(prev => {
      if (!prev) return prev;
      return {
        ...prev,
        appearance: {
          ...prev.appearance,
          [key]: value,
        },
      };
    });
    setHasChanges(true);
  };

  const updatePrivacySetting = (key: keyof SettingsData['privacy'], value: boolean) => {
    setSettings(prev => {
      if (!prev) return prev;
      return {
        ...prev,
        privacy: {
          ...prev.privacy,
          [key]: value,
        },
      };
    });
    setHasChanges(true);
  };

  if (isLoading) return <p className="p-8 text-sm text-gray-500">Ayarlar yükleniyor...</p>;

  return (
    <UsersSectionLayout activeTab="settings">
      <div className="space-y-6">
        <div className="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
          <h1 className="text-2xl font-bold text-gray-900">Ayarlar</h1>
          <p className="mt-2 text-sm text-gray-500">Uygulama ayarlarınızı yönetin.</p>
        </div>

        {/* Bildirim Ayarları */}
        <div className="rounded-2xl border border-gray-200 bg-white shadow-sm">
          <div className="border-b border-gray-200 px-6 py-5">
            <h2 className="text-lg font-semibold text-gray-900">Bildirimler</h2>
            <p className="text-sm text-gray-500 mt-1">Hangi bildirimleri almak istediğinizi seçin.</p>
          </div>
          <div className="space-y-4 p-6">
            <label className="flex items-center justify-between">
              <div>
                <p className="text-sm font-medium text-gray-900">E-posta Bildirimleri</p>
                <p className="text-xs text-gray-500">Önemli güncellemeler e-posta ile gönderilsin</p>
              </div>
              <input
                type="checkbox"
                checked={settings?.notifications?.emailNotifications ?? true}
                onChange={(e) => updateNotificationSetting('emailNotifications', e.target.checked)}
                className="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
              />
            </label>

            <label className="flex items-center justify-between">
              <div>
                <p className="text-sm font-medium text-gray-900">Push Bildirimleri</p>
                <p className="text-xs text-gray-500">Tarayıcı bildirimlerini göster</p>
              </div>
              <input
                type="checkbox"
                checked={settings?.notifications?.pushNotifications ?? true}
                onChange={(e) => updateNotificationSetting('pushNotifications', e.target.checked)}
                className="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
              />
            </label>

            <label className="flex items-center justify-between">
              <div>
                <p className="text-sm font-medium text-gray-900">Dava Güncellemeleri</p>
                <p className="text-xs text-gray-500">Dava ile ilgili değişikliklerden haberdar ol</p>
              </div>
              <input
                type="checkbox"
                checked={settings?.notifications?.caseUpdates ?? true}
                onChange={(e) => updateNotificationSetting('caseUpdates', e.target.checked)}
                className="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
              />
            </label>

            <label className="flex items-center justify-between">
              <div>
                <p className="text-sm font-medium text-gray-900">Görev Hatırlatıcıları</p>
                <p className="text-xs text-gray-500">Yaklaşan görevler için hatırlatma al</p>
              </div>
              <input
                type="checkbox"
                checked={settings?.notifications?.taskReminders ?? true}
                onChange={(e) => updateNotificationSetting('taskReminders', e.target.checked)}
                className="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
              />
            </label>
          </div>
        </div>

        {/* Görünüm Ayarları */}
        <div className="rounded-2xl border border-gray-200 bg-white shadow-sm">
          <div className="border-b border-gray-200 px-6 py-5">
            <h2 className="text-lg font-semibold text-gray-900">Görünüm</h2>
            <p className="text-sm text-gray-500 mt-1">Uygulama görünümünü kişiselleştirin.</p>
          </div>
          <div className="space-y-4 p-6">
            <div>
              <label className="block text-sm font-medium text-gray-900 mb-2">Tema</label>
              <select
                value={settings?.appearance?.theme ?? 'light'}
                onChange={(e) => updateAppearanceSetting('theme', e.target.value)}
                className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary focus:ring-primary"
              >
                <option value="light">Açık Tema</option>
                <option value="dark">Koyu Tema</option>
                <option value="auto">Otomatik</option>
              </select>
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-900 mb-2">Dil</label>
              <select
                value={settings?.appearance?.language ?? 'tr'}
                onChange={(e) => updateAppearanceSetting('language', e.target.value)}
                className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary focus:ring-primary"
              >
                <option value="tr">Türkçe</option>
                <option value="en">English</option>
              </select>
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-900 mb-2">Saat Dilimi</label>
              <select
                value={settings?.appearance?.timezone ?? 'Europe/Istanbul'}
                onChange={(e) => updateAppearanceSetting('timezone', e.target.value)}
                className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary focus:ring-primary"
              >
                <option value="Europe/Istanbul">İstanbul</option>
                <option value="Europe/Ankara">Ankara</option>
                <option value="Europe/Izmir">İzmir</option>
                <option value="UTC">UTC</option>
              </select>
            </div>
          </div>
        </div>

        {/* Gizlilik Ayarları */}
        <div className="rounded-2xl border border-gray-200 bg-white shadow-sm">
          <div className="border-b border-gray-200 px-6 py-5">
            <h2 className="text-lg font-semibold text-gray-900">Gizlilik</h2>
            <p className="text-sm text-gray-500 mt-1">Profil gizlilik ayarlarınızı yönetin.</p>
          </div>
          <div className="space-y-4 p-6">
            <label className="flex items-center justify-between">
              <div>
                <p className="text-sm font-medium text-gray-900">Profili Diğerlerine Göster</p>
                <p className="text-xs text-gray-500">Diğer kullanıcılar sizi görebilsin</p>
              </div>
              <input
                type="checkbox"
                checked={settings?.privacy?.showProfileToOthers ?? true}
                onChange={(e) => updatePrivacySetting('showProfileToOthers', e.target.checked)}
                className="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
              />
            </label>

            <label className="flex items-center justify-between">
              <div>
                <p className="text-sm font-medium text-gray-900">Çevrimiçi Durumunu Göster</p>
                <p className="text-xs text-gray-500">Diğerleri çevrimiçi olduğunuzu görsün</p>
              </div>
              <input
                type="checkbox"
                checked={settings?.privacy?.showOnlineStatus ?? true}
                onChange={(e) => updatePrivacySetting('showOnlineStatus', e.target.checked)}
                className="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
              />
            </label>
          </div>
        </div>

        {/* Kaydet Butonları */}
        <div className="flex justify-end gap-3">
          <button
            type="button"
            onClick={handleCancel}
            disabled={!hasChanges}
            className="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-bold text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Değişiklikleri Geri Al
          </button>
          <button
            type="button"
            onClick={handleSave}
            disabled={!hasChanges}
            className="rounded-lg bg-primary px-4 py-2 text-sm font-bold text-white hover:bg-primary/90 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Ayarları Kaydet
          </button>
        </div>
      </div>
    </UsersSectionLayout>
  );
};
