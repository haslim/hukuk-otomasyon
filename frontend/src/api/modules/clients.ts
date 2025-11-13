import { apiClient } from '../client';

export const ClientApi = {
  list: () => apiClient.get('/clients').then((res) => res.data),
  create: (payload: Record<string, unknown>) => apiClient.post('/clients', payload).then((res) => res.data),
};
