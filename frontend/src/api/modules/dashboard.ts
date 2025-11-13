import { apiClient } from '../client';

export const DashboardApi = {
  overview: () => apiClient.get('/dashboard').then((res) => res.data),
};
