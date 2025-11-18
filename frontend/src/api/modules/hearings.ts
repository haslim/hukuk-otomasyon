import { apiClient } from '../client';

export interface HearingItem {
  id: string;
  case_id: string;
  hearing_date: string;
  court: string;
  notes?: string;
}

export const HearingsApi = {
  listByCase: (caseId: string) =>
    apiClient.get(`/cases/${caseId}/hearings`).then((res) => res.data as HearingItem[]),
};

