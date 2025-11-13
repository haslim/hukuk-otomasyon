import { apiClient } from '../client';

export const DocumentApi = {
  search: (term: string) => apiClient.get('/documents/search', { params: { q: term } }).then((res) => res.data),
  listByCase: (caseId: string) => apiClient.get(`/cases/${caseId}/documents`).then((res) => res.data),
};
