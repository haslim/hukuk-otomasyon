import { apiClient } from '../client';

export interface WorkflowStepPayload {
  title: string;
  is_required: boolean;
}

export interface WorkflowTemplatePayload {
  name: string;
  case_type: string;
  tags?: string[];
  steps: WorkflowStepPayload[];
}

export const WorkflowApi = {
  templates: () => apiClient.get('/workflow/templates').then((res) => res.data),
  createTemplate: (payload: WorkflowTemplatePayload) =>
    apiClient.post('/workflow/templates', payload).then((res) => res.data),
};
