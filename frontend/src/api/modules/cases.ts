import { apiClient } from '../client';

export interface CasePayload {
  title: string;
  case_no: string;
  type: string;
  client_id: string;
  workflow_template_id?: string;
}

export const CaseApi = {
  list: () => apiClient.get('/cases').then((res) => res.data),
  create: (payload: CasePayload) => apiClient.post('/cases', payload).then((res) => res.data),
  attachWorkflow: (id: string, templateId: string) =>
    apiClient.post(`/cases/${id}/workflow`, { template_id: templateId }).then((res) => res.data),
};
