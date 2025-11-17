import { useState } from 'react';
import { CalendarApi, DayGroup } from '../../api/modules/calendar';
import { useAsyncData } from '../../hooks/useAsyncData';

export const CalendarPage = () => {
  const [viewMode, setViewMode] = useState<'Gün' | 'Hafta' | 'Ay'>('Hafta');

  const { data: eventsData, isLoading } = useAsyncData(['calendar-events'], () =>
    CalendarApi.getEvents(),
  );

  const mockDayGroups: DayGroup[] =
    eventsData ||
    [
      {
        date: '13 KASIM 2025, PERŞEMBE',
        dayName: '13 KASIM 2025, PERŞEMBE',
        events: [
          {
            id: '1',
            type: 'hearing',
            title: 'İstanbul 1. Asliye Hukuk Mahkemesi',
            description: 'Aydın Tekstil / Karşı Taraf Ticaret Davası',
            time: '10:30',
            date: '2025-11-13',
          },
          {
            id: '2',
            type: 'task',
            title: 'Cevap dilekçesi hazırlanacak',
            description: 'Dosya No: 2024/123 E.',
            time: '14:00',
            date: '2025-11-13',
          },
        ],
      },
      {
        date: '14 KASIM 2025, CUMA',
        dayName: '14 KASIM 2025, CUMA',
        events: [
          {
            id: '3',
            type: 'task',
            title: 'Bilirkişi raporuna itiraz edilecek',
            description: 'Dosya No: 2023/567 E.',
            time: '17:00',
            date: '2025-11-14',
          },
          {
            id: '4',
            type: 'hearing',
            title: 'Ankara 5. İdare Mahkemesi',
            description: 'Yılmaz İnşaat / Belediye iptal davası',
            time: '11:00',
            date: '2025-11-14',
          },
        ],
      },
      {
        date: '15 KASIM 2025, CUMARTESİ',
        dayName: '15 KASIM 2025, CUMARTESİ',
        events: [],
      },
    ];

  const getEventIcon = (type: 'hearing' | 'task') => {
    if (type === 'hearing') return 'gavel';
    if (type === 'task') return 'task_alt';
    return 'event';
  };

  const getEventIconColor = (type: 'hearing' | 'task') => {
    if (type === 'hearing') return 'text-gray-800';
    if (type === 'task') return 'text-gray-600';
    return 'text-gray-500';
  };

  if (isLoading) {
    return (
      <section className="flex h-full items-center justify-center">
        <p className="text-sm text-gray-500">Takvim yükleniyor...</p>
      </section>
    );
  }

  return (
    <section className="flex flex-col gap-6">
      {/* Üst başlık ve kontroller */}
      <div className="flex flex-wrap items-center justify-between gap-4">
        <div className="space-y-1">
          <h1 className="text-2xl font-bold text-gray-900">Takvim</h1>
          <p className="text-sm text-gray-500">
            Duruşmalarınızı, görevlerinizi ve önemli tarihleri tek ekrandan yönetin.
          </p>
        </div>

        <div className="flex items-center gap-3">
          <div className="flex h-10 items-center justify-center rounded-full bg-gray-100 px-1">
            <button
              type="button"
              className={`px-3 py-1.5 text-xs font-semibold rounded-full ${
                viewMode === 'Gün'
                  ? 'bg-white text-gray-900 shadow-sm'
                  : 'text-gray-500 hover:text-gray-800'
              }`}
              onClick={() => setViewMode('Gün')}
            >
              Gün
            </button>
            <button
              type="button"
              className={`px-3 py-1.5 text-xs font-semibold rounded-full ${
                viewMode === 'Hafta'
                  ? 'bg-white text-gray-900 shadow-sm'
                  : 'text-gray-500 hover:text-gray-800'
              }`}
              onClick={() => setViewMode('Hafta')}
            >
              Hafta
            </button>
            <button
              type="button"
              className={`px-3 py-1.5 text-xs font-semibold rounded-full ${
                viewMode === 'Ay'
                  ? 'bg-white text-gray-900 shadow-sm'
                  : 'text-gray-500 hover:text-gray-800'
              }`}
              onClick={() => setViewMode('Ay')}
            >
              Ay
            </button>
          </div>
          <button
            type="button"
            className="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary/90"
          >
            <span className="material-symbols-outlined text-base">add</span>
            Yeni etkinlik
          </button>
        </div>
      </div>

      {/* Günlük gruplar */}
      <div className="space-y-6">
        {mockDayGroups.map((dayGroup, index) => (
          <div
            key={index}
            className="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm"
          >
            <div className="flex items-center justify-between border-b border-gray-100 pb-3">
              <h3 className="text-sm font-semibold text-gray-800">{dayGroup.dayName}</h3>
              <span className="text-xs font-medium text-gray-400">
                {dayGroup.events.length} etkinlik
              </span>
            </div>

            {dayGroup.events.length > 0 ? (
              <div className="mt-4 flex flex-col gap-2">
                {dayGroup.events.map((event) => (
                  <div
                    key={event.id}
                    className="flex cursor-pointer items-center justify-between gap-4 rounded-xl border border-gray-100 bg-gray-50 px-4 py-3 hover:border-primary/40 hover:bg-white hover:shadow-sm transition-all"
                  >
                    <div className="flex items-center gap-4">
                      <div
                        className={`flex size-10 items-center justify-center rounded-lg bg-white ${getEventIconColor(
                          event.type,
                        )}`}
                      >
                        <span className="material-symbols-outlined text-xl">
                          {getEventIcon(event.type)}
                        </span>
                      </div>
                      <div className="flex flex-col">
                        <p className="text-sm font-semibold text-gray-900 line-clamp-1">
                          {event.title}
                        </p>
                        <p className="text-xs text-gray-500 line-clamp-2">
                          {event.description}
                        </p>
                      </div>
                    </div>
                    <div className="flex items-center gap-3">
                      <span className="rounded-full bg-white px-3 py-1 text-xs font-medium text-gray-800">
                        {event.time}
                      </span>
                      <button
                        type="button"
                        className="text-gray-400 hover:text-gray-700"
                      >
                        <span className="material-symbols-outlined text-base">more_vert</span>
                      </button>
                    </div>
                  </div>
                ))}
              </div>
            ) : (
              <div className="mt-4 flex flex-col items-center justify-center rounded-xl border border-dashed border-gray-200 bg-gray-50 py-8 text-center">
                <span className="material-symbols-outlined text-3xl text-gray-300">
                  event_busy
                </span>
                <p className="mt-2 text-sm font-medium text-gray-600">
                  Bu gün için planlanmış bir etkinlik bulunmuyor.
                </p>
              </div>
            )}
          </div>
        ))}
      </div>
    </section>
  );
};

