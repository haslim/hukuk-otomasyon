import { apiClient } from '../client';

export interface TaskPayload {
  title: string;
  due_date?: string;
  status?: 'open' | 'in_progress' | 'completed';
  case_id?: string;
}

export interface TaskItem extends TaskPayload {
  id: string;
  created_at?: string;
  updated_at?: string;
}

export const TaskApi = {
  listByCase: (caseId: string) =>
    apiClient.get('/tasks', { params: { case_id: caseId } }).then((res) => res.data as TaskItem[]),
  createForCase: (caseId: string, payload: Omit<TaskPayload, 'case_id'>) =>
    apiClient.post('/tasks', { ...payload, case_id: caseId }).then((res) => res.data as TaskItem),
};

