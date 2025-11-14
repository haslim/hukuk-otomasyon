import { apiClient } from '../client';

export interface CashStats {
  totalIncome: number;
  totalExpense: number;
  netBalance: number;
}

export interface CashTransaction {
  id: string;
  date: string;
  caseNumber?: string;
  clientName?: string;
  type: 'income' | 'expense';
  category: string;
  amount: number;
  description: string;
}

export const FinanceApi = {
  cashFlow: () => apiClient.get('/finance/cash-flow').then((res: any) => res.data),
  getCashStats: () => apiClient.get('/finance/cash-stats').then((res: any) => res.data),
  getCashTransactions: () => apiClient.get('/finance/cash-transactions').then((res: any) => res.data),
};
