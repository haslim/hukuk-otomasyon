import { apiClient } from '../client';

export interface CalendarEvent {
  id: string;
  type: 'hearing' | 'task';
  title: string;
  description: string;
  time: string;
  date: string;
  court?: string;
  caseNumber?: string;
}

export interface DayGroup {
  date: string;
  dayName: string;
  events: CalendarEvent[];
}

export const CalendarApi = {
  getEvents: (startDate?: string, endDate?: string) => 
    apiClient.get('/calendar/events', { params: { startDate, endDate } }).then((res: any) => res.data),
  
  createEvent: (event: Omit<CalendarEvent, 'id'>) => 
    apiClient.post('/calendar/events', event).then((res: any) => res.data),
  
  updateEvent: (id: string, event: Partial<CalendarEvent>) => 
    apiClient.put(`/calendar/events/${id}`, event).then((res: any) => res.data),
  
  deleteEvent: (id: string) => 
    apiClient.delete(`/calendar/events/${id}`).then((res: any) => res.data),
};