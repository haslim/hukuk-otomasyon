import { apiClient } from '../client';

export interface MediationFeeCalculation {
  id: string;
  case_id?: string;
  client_id?: string;
  calculation_type: 'standard' | 'commercial' | 'urgent';
  party_count: number;
  subject_value: number;
  base_fee: number;
  vat_rate: number;
  vat_amount: number;
  total_fee: number;
  fee_per_party: number;
  calculation_details?: {
    applicable_tariff: any;
    calculation_steps: string[];
  };
  calculation_date: string;
  created_at: string;
  updated_at: string;
}

export interface MediationFeeRequest {
  calculation_type?: 'standard' | 'commercial' | 'urgent';
  party_count: number;
  subject_value: number;
  vat_rate?: number;
  case_id?: string;
  client_id?: string;
}

export interface MediationFeeTariff {
  type: string;
  tariffs: Array<{
    min: number;
    max?: number;
    fee?: number;
    percentage?: number;
    party_rule: string;
  }>;
}

export const mediationFeesApi = {
  // Hesaplama yap
  calculate: async (data: MediationFeeRequest) => {
    const response = await apiClient.post('/mediation-fees/calculate', data);
    return response.data;
  },

  // Hesaplamayı kaydet
  store: async (data: MediationFeeRequest) => {
    const response = await apiClient.post('/mediation-fees', data);
    return response.data;
  },

  // Hesaplamaları listele
  index: async (filters?: {
    case_id?: string;
    client_id?: string;
    calculation_type?: string;
    date_from?: string;
    date_to?: string;
  }) => {
    const response = await apiClient.get('/mediation-fees', { params: filters });
    return response.data;
  },

  // Tek hesaplama getir
  show: async (id: string) => {
    const response = await apiClient.get(`/mediation-fees/${id}`);
    return response.data;
  },

  // Hesaplamayı sil
  destroy: async (id: string) => {
    const response = await apiClient.delete(`/mediation-fees/${id}`);
    return response.data;
  },

  // Tarifeleri getir
  tariffs: async () => {
    const response = await apiClient.get('/mediation-fees/tariffs');
    return response.data;
  },

  // Hesaplamadan fatura oluştur
  createInvoice: async (id: string, data: {
    client_id?: string;
    case_id?: string;
    issue_date?: string;
    due_date?: string;
    status?: string;
    notes?: string;
    client_details?: any;
  }) => {
    const response = await apiClient.post(`/mediation-fees/${id}/create-invoice`, data);
    return response.data;
  }
};
