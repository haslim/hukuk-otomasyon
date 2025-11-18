import { apiClient } from '../client';

export interface CalendarEvent {
  id: string;
  title: string;
  description: string;
  start: string;
  end?: string;
  type: 'hearing' | 'task' | 'google';
  caseNumber?: string;
  caseId?: string;
  court?: string;
  source?: 'backend' | 'google';
}

const normalizeDateTime = (value?: string) => {
  if (!value) {
    return '';
  }

  if (value.includes('T')) {
    return value;
  }

  if (value.includes(' ')) {
    return value.replace(' ', 'T');
  }

  return `${value}T00:00:00`;
};

export const CalendarApi = {
  getEvents: (startDate?: string, endDate?: string) =>
    apiClient
      .get('/calendar/events', { params: { startDate, endDate } })
      .then((res: any) => {
        if (!Array.isArray(res.data)) {
          return [];
        }

        return res.data.map((event: any) => ({
          id: event.id,
          title: event.title ?? 'Takvim Etkinliği',
          description: event.description ?? '',
          start: normalizeDateTime(event.start),
          end: normalizeDateTime(event.end ?? event.start),
          type: event.type === 'hearing' ? 'hearing' : 'task',
          caseNumber: event.caseNumber,
          caseId: event.caseId,
          court: event.court,
          source: 'backend' as const,
        }));
      }),
};
