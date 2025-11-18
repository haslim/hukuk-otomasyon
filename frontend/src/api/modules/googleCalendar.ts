import type { CalendarEvent } from './calendar';

const GOOGLE_CALENDAR_ID = import.meta.env.VITE_GOOGLE_CALENDAR_ID ?? '';
const GOOGLE_API_KEY = import.meta.env.VITE_GOOGLE_CALENDAR_API_KEY ?? '';

export interface GoogleCalendarEventItem {
  id: string;
  summary?: string;
  description?: string;
  start: { date?: string; dateTime?: string };
  end: { date?: string; dateTime?: string };
  location?: string;
  extendedProperties?: {
    private?: Record<string, string>;
  };
}

const toGoogleDateTime = (value: string, endOfDay = false) => {
  if (value.includes('T')) {
    return value;
  }
  return `${value}T${endOfDay ? '23:59:59' : '00:00:00'}Z`;
};

const buildTimeRange = (startDate?: string, endDate?: string) => {
  const params: Record<string, string> = {
    key: GOOGLE_API_KEY,
    singleEvents: 'true',
    orderBy: 'startTime',
    maxResults: '250',
  };

  if (startDate) {
    params.timeMin = toGoogleDateTime(startDate);
  }
  if (endDate) {
    params.timeMax = toGoogleDateTime(endDate, true);
  }

  return params;
};

const normalizeCalendarDateTime = (value?: string) => {
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

const mapToCalendarEvent = (event: GoogleCalendarEventItem): CalendarEvent => {
  const start = event.start.dateTime ?? event.start.date ?? '';
  const end = event.end.dateTime ?? event.end.date ?? start;

  return {
    id: event.id,
    title: event.summary ?? 'Google Calendar Etkinliği',
    description: event.description ?? '',
    start: normalizeCalendarDateTime(start),
    end: normalizeCalendarDateTime(end),
    type: 'google',
    source: 'google',
  };
};

export const GoogleCalendarApi = {
  getEvents: async (startDate?: string, endDate?: string) => {
    if (!GOOGLE_CALENDAR_ID || !GOOGLE_API_KEY) {
      return [];
    }

    const baseUrl = `https://www.googleapis.com/calendar/v3/calendars/${encodeURIComponent(
      GOOGLE_CALENDAR_ID,
    )}/events`;
    const params = new URLSearchParams(buildTimeRange(startDate, endDate));

    const response = await fetch(`${baseUrl}?${params.toString()}`);
    if (!response.ok) {
      console.warn('Google Calendar API returned', response.status, response.statusText);
      return [];
    }

    const payload = (await response.json()) as { items?: GoogleCalendarEventItem[] };
    if (!Array.isArray(payload.items)) {
      return [];
    }

    return payload.items.map((item) => mapToCalendarEvent(item));
  },
};
