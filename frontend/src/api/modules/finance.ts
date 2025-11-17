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
  getCashStats: () =>
    apiClient.get('/finance/cash-stats').then((res: any) => {
      const data = res.data || {};
      return {
        totalIncome: data.total_income ?? 0,
        totalExpense: data.total_expense ?? 0,
        netBalance: data.net_balance ?? 0,
      } as CashStats;
    }),
  getCashTransactions: () =>
    apiClient.get('/finance/cash-transactions').then((res: any) => {
      const items = (res.data ?? []) as any[];
      return items.map(
        (item): CashTransaction => ({
          id: item.id,
          date: item.occurred_on ?? item.date ?? '',
          caseNumber: item.case_no ?? item.caseNumber,
          clientName: item.client_name ?? item.clientName,
          type: item.type,
          category: item.category ?? '',
          amount: Number(item.amount ?? 0),
          description: item.description ?? '',
        }),
      );
    }),
};
