import { useEffect, useMemo, useState } from 'react';
import { CalendarApi, CalendarEvent } from '../../api/modules/calendar';
import { GoogleCalendarApi } from '../../api/modules/googleCalendar';
import { useAsyncData } from '../../hooks/useAsyncData';

const WEEKDAYS = ['Pts', 'Sal', 'Çar', 'Per', 'Cum', 'Cmt', 'Paz'];

const formatIsoDay = (value?: string | Date) => {
  if (!value) return '';
  const date = value instanceof Date ? value : new Date(value);
  if (Number.isNaN(date.getTime())) return '';
  return date.toISOString().slice(0, 10);
};

const buildMonthMatrix = (month: Date, eventsByDay: Record<string, CalendarEvent[]>) => {
  const firstOfMonth = new Date(month.getFullYear(), month.getMonth(), 1);
  const lastOfMonth = new Date(month.getFullYear(), month.getMonth() + 1, 0);
  const firstDayIndex = (firstOfMonth.getDay() + 6) % 7;
  const totalDays = lastOfMonth.getDate();
  const totalCells = Math.ceil((firstDayIndex + totalDays) / 7) * 7;

  const cells = [];
  for (let i = 0; i < totalCells; i += 1) {
    const date = new Date(firstOfMonth);
    date.setDate(i - firstDayIndex + 1);
    const isoKey = formatIsoDay(date);
    cells.push({
      iso: isoKey,
      day: date.getDate(),
      date,
      inCurrentMonth: date.getMonth() === month.getMonth(),
      events: isoKey ? eventsByDay[isoKey] ?? [] : [],
    });
  }

  const weeks = [];
  for (let i = 0; i < cells.length; i += 7) {
    weeks.push(cells.slice(i, i + 7));
  }

  return weeks;
};

const formatEventTime = (value?: string) => {
  if (!value) return 'Tüm gün';
  const normalized = value.includes('T') ? value : value.replace(' ', 'T');
  const date = new Date(normalized);
  if (Number.isNaN(date.getTime())) {
    return 'Tüm gün';
  }
  return date.toLocaleTimeString('tr-TR', { hour: '2-digit', minute: '2-digit' });
};

const getEventIcon = (type: CalendarEvent['type']) => {
  if (type === 'hearing') return 'gavel';
  if (type === 'task') return 'task_alt';
  return 'event';
};

const getEventIconColor = (type: CalendarEvent['type']) => {
  if (type === 'hearing') return 'text-gray-800';
  if (type === 'task') return 'text-gray-600';
  return 'text-sky-500';
};

export const CalendarPage = () => {
  const today = useMemo(() => new Date(), []);
  const [viewMode, setViewMode] = useState<'Gün' | 'Hafta' | 'Ay'>('Ay');
  const [currentMonth, setCurrentMonth] = useState<Date>(
    () => new Date(today.getFullYear(), today.getMonth(), 1),
  );
  const [selectedDay, setSelectedDay] = useState(() => formatIsoDay(today));

  const monthStart = useMemo(
    () => new Date(currentMonth.getFullYear(), currentMonth.getMonth(), 1),
    [currentMonth],
  );
  const monthEnd = useMemo(
    () => new Date(currentMonth.getFullYear(), currentMonth.getMonth() + 1, 0),
    [currentMonth],
  );

  const startKey = formatIsoDay(monthStart);
  const endKey = formatIsoDay(monthEnd);

  const { data: backendEvents = [], isLoading: backendLoading } = useAsyncData<CalendarEvent[]>(
    ['calendar-events', startKey, endKey],
    () => CalendarApi.getEvents(startKey, endKey),
  );

  const { data: googleEvents = [], isLoading: googleLoading } = useAsyncData<CalendarEvent[]>(
    ['google-calendar-events', startKey, endKey],
    () => GoogleCalendarApi.getEvents(startKey, endKey),
  );

  useEffect(() => {
    setSelectedDay((prev) => {
      const prevDate = prev ? new Date(prev) : monthStart;
      if (
        prevDate.getFullYear() === currentMonth.getFullYear() &&
        prevDate.getMonth() === currentMonth.getMonth()
      ) {
        return formatIsoDay(prevDate);
      }

      const maxDay = new Date(currentMonth.getFullYear(), currentMonth.getMonth() + 1, 0).getDate();
      const day = Math.min(prevDate.getDate(), maxDay);
      const next = new Date(currentMonth);
      next.setDate(day);
      return formatIsoDay(next);
    });
  }, [currentMonth, monthStart]);

  const combinedEvents = useMemo<CalendarEvent[]>(() => {
    const normalizedGoogle = googleEvents.map((event) => ({
      ...event,
      source: 'google' as const,
    }));

    return [...backendEvents, ...normalizedGoogle];
  }, [backendEvents, googleEvents]);

  const eventsByDay = useMemo<Record<string, CalendarEvent[]>>(() => {
    const map: Record<string, CalendarEvent[]> = {};
    combinedEvents.forEach((event) => {
      const key = formatIsoDay(event.start);
      if (!key) {
        return;
      }
      if (!map[key]) {
        map[key] = [];
      }
      map[key].push(event);
    });

    Object.values(map).forEach((list) => {
      list.sort((a, b) => (a.start > b.start ? 1 : -1));
    });

    return map;
  }, [combinedEvents]);

  const calendarWeeks = useMemo(
    () => buildMonthMatrix(currentMonth, eventsByDay),
    [currentMonth, eventsByDay],
  );

  const selectedEvents = selectedDay ? eventsByDay[selectedDay] ?? [] : [];

  const loading = backendLoading || googleLoading;

  const monthLabel = monthStart.toLocaleDateString('tr-TR', {
    month: 'long',
    year: 'numeric',
  });

  if (loading) {
    return (
      <section className="flex h-full items-center justify-center">
        <p className="text-sm text-gray-500">Takvim yükleniyor...</p>
      </section>
    );
  }

  return (
    <section className="flex flex-col gap-6">
      <div className="flex flex-wrap items-center justify-between gap-4">
        <div className="space-y-1">
          <h1 className="text-2xl font-bold text-gray-900">Takvim</h1>
          <p className="text-sm text-gray-500">
            Duruşmalarınızı, görevlerinizi ve önemli tarihleri tek ekrandan yönetin.
          </p>
        </div>

        <div className="flex items-center gap-3">
          <div className="flex h-10 items-center justify-center rounded-full bg-gray-100 px-1">
            {(['Gün', 'Hafta', 'Ay'] as const).map((mode) => (
              <button
                key={mode}
                type="button"
                className={`px-3 py-1.5 text-xs font-semibold rounded-full ${
                  viewMode === mode
                    ? 'bg-white text-gray-900 shadow-sm'
                    : 'text-gray-500 hover:text-gray-800'
                }`}
                onClick={() => setViewMode(mode)}
              >
                {mode}
              </button>
            ))}
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

      {viewMode === 'Ay' ? (
        <div className="grid gap-6 lg:grid-cols-[3fr,2fr]">
          <div className="rounded-2xl border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark p-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-xs uppercase text-gray-400 tracking-[0.2em]">Ay</p>
                <h2 className="text-2xl font-semibold text-gray-900">{monthLabel}</h2>
              </div>
              <div className="inline-flex items-center gap-2 rounded-full border border-gray-200 bg-white px-2 py-1 text-sm shadow-sm">
                <button
                  type="button"
                  className="text-gray-500 hover:text-gray-900"
                  onClick={() =>
                    setCurrentMonth(
                      (prev) => new Date(prev.getFullYear(), prev.getMonth() - 1, 1),
                    )
                  }
                >
                  <span className="material-symbols-outlined text-base">chevron_left</span>
                </button>
                <button
                  type="button"
                  className="text-gray-500 hover:text-gray-900"
                  onClick={() =>
                    setCurrentMonth(
                      (prev) => new Date(prev.getFullYear(), prev.getMonth() + 1, 1),
                    )
                  }
                >
                  <span className="material-symbols-outlined text-base">chevron_right</span>
                </button>
              </div>
            </div>

            <div className="mt-4 grid grid-cols-7 gap-2 text-center text-xs font-semibold uppercase text-gray-400">
              {WEEKDAYS.map((day) => (
                <div key={day}>{day}</div>
              ))}
            </div>

            <div className="mt-4 space-y-2">
              {calendarWeeks.map((week, weekIndex) => (
                <div key={`week-${weekIndex}`} className="grid grid-cols-7 gap-2">
                  {week.map((cell) => {
                    const isSelected = cell.iso === selectedDay;
                    const isToday = cell.iso === formatIsoDay(today);
                    const baseClass =
                      'flex flex-col justify-between rounded-xl border p-3 text-left transition-all';
                    const selectedClass = isSelected
                      ? 'border-primary/60 bg-primary/5 text-primary'
                      : 'border-transparent bg-white text-text-light shadow-sm';
                    const outsideClass = cell.inCurrentMonth ? '' : 'opacity-40';
                    return (
                      <button
                        key={cell.iso || `${weekIndex}-${cell.day}`}
                        type="button"
                        onClick={() => cell.iso && setSelectedDay(cell.iso)}
                        className={`${baseClass} ${selectedClass} ${outsideClass}`}
                        disabled={!cell.iso}
                      >
                        <div className="flex items-center justify-between text-sm font-semibold">
                          <span>{cell.day}</span>
                          {isToday && (
                            <span className="text-xs font-medium text-primary">Bugün</span>
                          )}
                        </div>
                        {cell.events.length > 0 && (
                          <p className="mt-2 text-[10px] font-semibold text-gray-500">
                            {cell.events.length} etkinlik
                          </p>
                        )}
                      </button>
                    );
                  })}
                </div>
              ))}
            </div>
          </div>

          <div className="rounded-2xl border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark p-6">
            <p className="text-xs uppercase text-gray-400 tracking-[0.2em]">Seçilen gün</p>
            <h2 className="mt-1 text-xl font-semibold text-gray-900">
              {selectedDay
                ? new Date(selectedDay).toLocaleDateString('tr-TR', {
                    weekday: 'long',
                    day: 'numeric',
                    month: 'long',
                  })
                : 'Gün seçin'}
            </h2>

            {selectedEvents.length === 0 ? (
              <div className="mt-6 flex flex-col items-center justify-center gap-2 rounded-xl border border-dashed border-gray-200 bg-white px-4 py-8 text-center">
                <span className="material-symbols-outlined text-3xl text-gray-300">
                  event_busy
                </span>
                <p className="text-sm text-gray-500">
                  Bu gün için henüz planlanan etkinlik yok.
                </p>
              </div>
            ) : (
              <div className="mt-6 space-y-3">
                {selectedEvents.map((event) => (
                  <div
                    key={event.id}
                    className="flex items-center justify-between gap-4 rounded-xl border border-gray-100 bg-white px-4 py-3 shadow-sm"
                  >
                    <div className="flex items-center gap-3">
                      <div
                        className={`flex h-10 w-10 items-center justify-center rounded-lg bg-white ${getEventIconColor(
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
                        {event.caseNumber && (
                          <p className="text-xs font-medium text-primary">
                            Dosya: {event.caseNumber}
                          </p>
                        )}
                      </div>
                    </div>
                    <div className="flex flex-col items-end text-right">
                      <span className="text-xs font-medium text-gray-500">
                        {formatEventTime(event.start)}
                      </span>
                      <span className="text-xs text-gray-400">
                        {event.source === 'google' ? 'Google Takvimi' : 'Sistem takvimi'}
                      </span>
                    </div>
                  </div>
                ))}
              </div>
            )}
          </div>
        </div>
      ) : (
        <div className="rounded-2xl border border-dashed border-gray-200 bg-white p-6 text-center text-sm text-gray-500">
          Gün ve hafta görünümleri yakında kullanıma alınacak. Şimdilik ay görünümünde kalabilirsiniz.
        </div>
      )}
    </section>
  );
};
