import { apiClient } from '../client';

export const WorkflowApi = {
  templates: () => apiClient.get('/workflow/templates').then((res) => res.data),
};
