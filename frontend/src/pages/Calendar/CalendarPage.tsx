import { useState } from 'react';
import { CalendarApi, CalendarEvent, DayGroup } from '../../api/modules/calendar';
import { useAsyncData } from '../../hooks/useAsyncData';

export const CalendarPage = () => {
  const [viewMode, setViewMode] = useState<'Gün' | 'Hafta' | 'Ay'>('Hafta');

  const { data: eventsData, isLoading } = useAsyncData(['calendar-events'], () => CalendarApi.getEvents());

  if (isLoading) return <p>Takvim yükleniyor...</p>;

  const mockDayGroups: DayGroup[] = eventsData || [
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
          date: '2025-11-13'
        },
        {
          id: '2',
          type: 'task',
          title: 'Cevap Dilekçesi Hazırlanacak',
          description: 'Dosya No: 2024/123 E.',
          time: '14:00',
          date: '2025-11-13'
        }
      ]
    },
    {
      date: '14 KASIM 2025, CUMA',
      dayName: '14 KASIM 2025, CUMA',
      events: [
        {
          id: '3',
          type: 'task',
          title: 'Bilirkişi Raporuna İtiraz Edilecek',
          description: 'Dosya No: 2023/567 E.',
          time: '17:00',
          date: '2025-11-14'
        },
        {
          id: '4',
          type: 'hearing',
          title: 'Ankara 5. İdare Mahkemesi',
          description: 'Yılmaz İnşaat / Belediye İptal Davası',
          time: '11:00',
          date: '2025-11-14'
        }
      ]
    },
    {
      date: '15 KASIM 2025, CUMARTESİ',
      dayName: '15 KASIM 2025, CUMARTESİ',
      events: []
    }
  ];

  const getEventIcon = (type: 'hearing' | 'task') => {
    switch (type) {
      case 'hearing':
        return 'gavel';
      case 'task':
        return 'task_alt';
      default:
        return 'event';
    }
  };

  const getEventIconColor = (type: 'hearing' | 'task') => {
    switch (type) {
      case 'hearing':
        return 'text-gray-800 dark:text-gray-200';
      case 'task':
        return 'text-gray-600 dark:text-gray-300';
      default:
        return 'text-gray-500';
    }
  };

  return (
    <div className="flex h-full min-h-screen w-full">
      {/* SideNavBar */}
      <nav className="flex h-screen min-h-[700px] w-64 flex-col justify-between border-r border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900/50 sticky top-0">
        <div className="flex flex-col gap-4">
          <div className="flex items-center gap-3">
            <div 
              className="bg-center bg-no-repeat aspect-square bg-cover rounded-full size-10" 
              data-alt="BGAofis company logo" 
              style={{
                backgroundImage: 'url("https://lh3.googleusercontent.com/aida-public/AB6AXuBK6W4RQCR84t5C-dFsGP0BFE41d_Sln5IajhX256zjIoEH7SU4UvCN4IeGSh8cq2p2zZZ1itaI3XBQOWro-O9dleEJYXvqP423C_-vcaHNaDH-wdqKd82mNivdWG36nhIVrPXlKbvEHgfsFOm5DRbmJR1-pRAe_pfxJTxiDCHgG0nMiuh9LmJV5mk_-IRyym6L98fUChjmKXhaGfEITx7yBDHH4q0E2WIFf61z5rZdrGRF2KsYd8b6ursBxcXcAgEdfiRZHoKs4juW")'
              }}
            />
            <div className="flex flex-col">
              <h1 className="text-gray-900 dark:text-gray-100 text-base font-medium leading-normal">BGAofis</h1>
              <p className="text-gray-500 dark:text-gray-400 text-sm font-normal leading-normal">Hukuk Bürosu Otomasyonu</p>
            </div>
          </div>
          <div className="flex flex-col gap-2 mt-4">
            <a className="flex items-center gap-3 rounded-lg px-3 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800" href="#">
              <span className="material-symbols-outlined text-gray-800 dark:text-gray-200">dashboard</span>
              <p className="text-sm font-medium leading-normal">Kontrol Paneli</p>
            </a>
            <a className="flex items-center gap-3 rounded-lg px-3 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800" href="#">
              <span className="material-symbols-outlined text-gray-800 dark:text-gray-200">folder</span>
              <p className="text-sm font-medium leading-normal">Dosyalar</p>
            </a>
            <a className="flex items-center gap-3 rounded-lg bg-primary/10 px-3 py-2 text-primary dark:bg-primary/20" href="#">
              <span className="material-symbols-outlined fill text-primary">calendar_month</span>
              <p className="text-sm font-medium leading-normal">Takvim</p>
            </a>
            <a className="flex items-center gap-3 rounded-lg px-3 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800" href="#">
              <span className="material-symbols-outlined text-gray-800 dark:text-gray-200">group</span>
              <p className="text-sm font-medium leading-normal">Müvekkiller</p>
            </a>
            <a className="flex items-center gap-3 rounded-lg px-3 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800" href="#">
              <span className="material-symbols-outlined text-gray-800 dark:text-gray-200">monitoring</span>
              <p className="text-sm font-medium leading-normal">Raporlar</p>
            </a>
          </div>
        </div>
        <button className="flex min-w-[84px] max-w-[480px] cursor-pointer items-center justify-center overflow-hidden rounded-lg h-10 px-4 bg-primary text-white text-sm font-bold leading-normal tracking-[0.015em] hover:bg-primary/90">
          <span className="truncate">+ Yeni Ekle</span>
        </button>
      </nav>

      {/* Main Content */}
      <main className="flex-1 p-8">
        <div className="mx-auto max-w-4xl">
          {/* PageHeading */}
          <div className="flex flex-wrap items-center justify-between gap-4">
            <p className="text-gray-900 dark:text-white text-4xl font-black leading-tight tracking-[-0.033em]">Takvim</p>
            
            {/* SegmentedButtons */}
            <div className="flex h-10 w-full max-w-xs items-center justify-center rounded-lg bg-gray-200/80 p-1 dark:bg-gray-800">
              <label className="flex h-full grow cursor-pointer items-center justify-center overflow-hidden rounded-md px-2 text-sm font-medium leading-normal text-gray-500 has-[:checked]:bg-white has-[:checked]:text-gray-900 has-[:checked]:shadow-sm dark:text-gray-400 dark:has-[:checked]:bg-gray-700 dark:has-[:checked]:text-white">
                <span className="truncate">Gün</span>
                <input 
                  className="invisible w-0" 
                  name="view-toggle" 
                  type="radio" 
                  value="Gün"
                  checked={viewMode === 'Gün'}
                  onChange={() => setViewMode('Gün')}
                />
              </label>
              <label className="flex h-full grow cursor-pointer items-center justify-center overflow-hidden rounded-md px-2 text-sm font-medium leading-normal text-gray-500 has-[:checked]:bg-white has-[:checked]:text-gray-900 has-[:checked]:shadow-sm dark:text-gray-400 dark:has-[:checked]:bg-gray-700 dark:has-[:checked]:text-white">
                <span className="truncate">Hafta</span>
                <input 
                  className="invisible w-0" 
                  name="view-toggle" 
                  type="radio" 
                  value="Hafta"
                  checked={viewMode === 'Hafta'}
                  onChange={() => setViewMode('Hafta')}
                />
              </label>
              <label className="flex h-full grow cursor-pointer items-center justify-center overflow-hidden rounded-md px-2 text-sm font-medium leading-normal text-gray-500 has-[:checked]:bg-white has-[:checked]:text-gray-900 has-[:checked]:shadow-sm dark:text-gray-400 dark:has-[:checked]:bg-gray-700 dark:has-[:checked]:text-white">
                <span className="truncate">Ay</span>
                <input 
                  className="invisible w-0" 
                  name="view-toggle" 
                  type="radio" 
                  value="Ay"
                  checked={viewMode === 'Ay'}
                  onChange={() => setViewMode('Ay')}
                />
              </label>
            </div>
          </div>

          <div className="mt-8 flex flex-col gap-8">
            {mockDayGroups.map((dayGroup, index) => (
              <div key={index}>
                {/* SectionHeader */}
                <h3 className="text-gray-900 dark:text-white text-lg font-bold leading-tight tracking-[-0.015em] pb-3 border-b border-gray-200 dark:border-gray-800">
                  {dayGroup.dayName}
                </h3>
                
                {dayGroup.events.length > 0 ? (
                  <div className="mt-4 flex flex-col gap-2">
                    {dayGroup.events.map((event) => (
                      <div 
                        key={event.id}
                        className="flex cursor-pointer items-center gap-4 rounded-lg bg-white p-4 min-h-[72px] justify-between shadow-sm hover:shadow-md transition-shadow dark:bg-gray-900/50"
                      >
                        <div className="flex items-center gap-4">
                          <div className={`flex items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-800 shrink-0 size-12 ${getEventIconColor(event.type)}`}>
                            <span className="material-symbols-outlined">
                              {getEventIcon(event.type)}
                            </span>
                          </div>
                          <div className="flex flex-col justify-center">
                            <p className="text-gray-900 dark:text-white text-base font-medium leading-normal line-clamp-1">
                              {event.title}
                            </p>
                            <p className="text-gray-500 dark:text-gray-400 text-sm font-normal leading-normal line-clamp-2">
                              {event.description}
                            </p>
                          </div>
                        </div>
                        <div className="flex items-center gap-4 shrink-0">
                          <p className="text-base font-medium text-gray-800 dark:text-gray-200">
                            {event.time}
                          </p>
                          <button className="text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-white">
                            <span className="material-symbols-outlined">more_vert</span>
                          </button>
                        </div>
                      </div>
                    ))}
                  </div>
                ) : (
                  /* Empty State */
                  <div className="mt-4 flex flex-col items-center justify-center rounded-lg border-2 border-dashed border-gray-300 bg-white dark:bg-gray-900/50 dark:border-gray-700 py-12 text-center">
                    <span className="material-symbols-outlined text-4xl text-gray-400 dark:text-gray-500">
                      event_busy
                    </span>
                    <p className="mt-2 text-sm font-medium text-gray-600 dark:text-gray-300">
                      Bu tarih için planlanmış bir etkinlik bulunmuyor.
                    </p>
                  </div>
                )}
              </div>
            ))}
          </div>
        </div>
      </main>
    </div>
  );
};