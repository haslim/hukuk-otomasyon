import { apiClient } from '../client';

export const FinanceApi = {
  cashFlow: () => apiClient.get('/finance/cash-flow').then((res) => res.data),
};
