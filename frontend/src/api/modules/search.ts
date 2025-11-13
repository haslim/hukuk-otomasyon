import { apiClient } from '../client';

export const SearchApi = {
  global: (term: string) => apiClient.get('/search', { params: { q: term } }).then((res) => res.data),
};
