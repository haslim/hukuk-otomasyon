import { apiClient } from '../client';

export const NotificationApi = {
  list: () => apiClient.get('/notifications').then((res) => res.data),
};
