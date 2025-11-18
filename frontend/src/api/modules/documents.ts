import { apiClient } from '../client';

export interface DocumentPayload {
  title: string;
  type: string;
  content?: string;
  tags?: string[];
}

export const DocumentApi = {
  search: (term: string) => apiClient.get('/documents/search', { params: { q: term } }).then((res) => res.data),
  listByCase: (caseId: string) => apiClient.get(`/cases/${caseId}/documents`).then((res) => res.data),
  createForCase: (caseId: string, payload: DocumentPayload) =>
    apiClient.post(`/cases/${caseId}/documents`, payload).then((res) => res.data),
};

