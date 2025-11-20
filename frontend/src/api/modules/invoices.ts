import { apiClient } from '../client';

export interface Invoice {
  id: string;
  invoice_number: string;
  calculation_id?: string;
  client_id: string;
  case_id?: string;
  issue_date: string;
  due_date: string;
  status: 'draft' | 'sent' | 'paid' | 'overdue' | 'cancelled';
  subtotal: number;
  vat_rate: number;
  vat_amount: number;
  total_amount: number;
  paid_amount: number;
  notes?: string;
  client_details?: any;
  created_at: string;
  updated_at: string;
  items?: InvoiceItem[];
  payments?: InvoicePayment[];
  client?: any;
  case?: any;
  calculation?: any;
}

export interface InvoiceItem {
  id: string;
  invoice_id: string;
  item_type: 'fee' | 'expense' | 'tax' | 'other';
  description: string;
  quantity: number;
  unit_price: number;
  line_total: number;
  vat_rate: number;
  vat_amount: number;
  reference_id?: string;
  created_at: string;
  updated_at: string;
}

export interface InvoicePayment {
  id: string;
  invoice_id: string;
  amount: number;
  payment_date: string;
  payment_method: 'cash' | 'bank_transfer' | 'credit_card' | 'check';
  payment_reference?: string;
  notes?: string;
  created_at: string;
  updated_at: string;
}

export interface InvoiceRequest {
  calculation_id?: string;
  client_id: string;
  case_id?: string;
  issue_date?: string;
  due_date?: string;
  status?: string;
  subtotal?: number;
  vat_rate?: number;
  vat_amount?: number;
  total_amount?: number;
  notes?: string;
  client_details?: any;
  items?: Partial<InvoiceItem>[];
}

export interface InvoiceStats {
  total_invoices: number;
  draft_invoices: number;
  sent_invoices: number;
  paid_invoices: number;
  overdue_invoices: number;
  total_amount: number;
  paid_amount: number;
  unpaid_amount: number;
}

export const invoicesApi = {
  // Faturaları listele
  index: async (filters?: {
    client_id?: string;
    case_id?: string;
    status?: string;
    date_from?: string;
    date_to?: string;
    search?: string;
  }) => {
    const response = await apiClient.get('/api/invoices', { params: filters });
    return response.data;
  },

  // Yeni fatura oluştur
  store: async (data: InvoiceRequest) => {
    const response = await apiClient.post('/api/invoices', data);
    return response.data;
  },

  // Fatura detayı
  show: async (id: string) => {
    const response = await apiClient.get(`/api/invoices/${id}`);
    return response.data;
  },

  // Fatura güncelle
  update: async (id: string, data: Partial<InvoiceRequest>) => {
    const response = await apiClient.put(`/api/invoices/${id}`, data);
    return response.data;
  },

  // Fatura sil
  destroy: async (id: string) => {
    const response = await apiClient.delete(`/api/invoices/${id}`);
    return response.data;
  },

  // Ödeme ekle
  addPayment: async (id: string, data: {
    amount: number;
    payment_date: string;
    payment_method: 'cash' | 'bank_transfer' | 'credit_card' | 'check';
    payment_reference?: string;
    notes?: string;
  }) => {
    const response = await apiClient.post(`/api/invoices/${id}/payments`, data);
    return response.data;
  },

  // Durum güncelle
  updateStatus: async (id: string, status: string) => {
    const response = await apiClient.patch(`/api/invoices/${id}/status`, { status });
    return response.data;
  },

  // İstatistikler
  getStats: async () => {
    const response = await apiClient.get('/api/invoices/stats');
    return response.data;
  },

  // PDF oluştur
  generatePdf: async (id: string) => {
    const response = await apiClient.get(`/api/invoices/${id}/pdf`);
    return response.data;
  },

  // Fatura gönder
  sendInvoice: async (id: string, data?: {
    to?: string;
    subject?: string;
    message?: string;
  }) => {
    const response = await apiClient.post(`/api/invoices/${id}/send`, data);
    return response.data;
  }
};
