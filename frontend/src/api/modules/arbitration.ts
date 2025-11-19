import { apiClient } from '../client';

export interface ArbitrationApplication {
  id: string;
  application_no: string;
  applicant_name: string;
  respondent_name: string;
  application_type: string;
  application_type_label: string;
  subject_matter: string;
  monetary_value?: number;
  formatted_monetary_value: string;
  currency: string;
  application_date: string;
  formatted_application_date: string;
  status: string;
  status_label: string;
  created_by?: {
    id: string;
    name: string;
  };
  mediator?: {
    id: string;
    name: string;
  };
  notes?: string;
  created_at: string;
  updated_at: string;
}

export interface ArbitrationApplicationDetail extends ArbitrationApplication {
  applicant_info: any;
  respondent_info: any;
  metadata?: any;
  documents: ApplicationDocument[];
  timeline: TimelineEvent[];
}

export interface ApplicationDocument {
  id: string;
  title: string;
  document_type: string;
  document_type_label: string;
  file_size: number;
  formatted_file_size: string;
  mime_type: string;
  is_public: boolean;
  file_icon: string;
  download_url: string;
  preview_url?: string;
  uploaded_by?: {
    id: string;
    name: string;
  };
  created_at: string;
}

export interface TimelineEvent {
  id: string;
  event_type: string;
  description: string;
  icon: string;
  color: string;
  event_data?: any;
  user?: {
    id: string;
    name: string;
  };
  formatted_time: string;
  time_ago: string;
  is_system_event: boolean;
  is_user_event: boolean;
  created_at: string;
}

export interface ArbitrationStatistics {
  total: number;
  pending: number;
  in_progress: number;
  completed: number;
  rejected: number;
  this_month: number;
  last_month: number;
  by_type: Record<string, number>;
  by_status: Record<string, number>;
  success_rate: number;
}

export interface CreateApplicationData {
  applicant_info: any;
  respondent_info: any;
  application_type: string;
  subject_matter: string;
  monetary_value?: number;
  currency?: string;
  application_date?: string;
  notes?: string;
}

export interface UpdateApplicationData {
  applicant_info?: any;
  respondent_info?: any;
  application_type?: string;
  subject_matter?: string;
  monetary_value?: number;
  currency?: string;
  application_date?: string;
  status?: string;
  mediator_id?: string;
  notes?: string;
}

export interface ApplicationFilters {
  status?: string;
  application_type?: string;
  mediator_id?: string;
  search?: string;
  date_from?: string;
  date_to?: string;
  created_by?: string;
  sort_by?: string;
  sort_order?: 'asc' | 'desc';
  per_page?: number;
}

export const arbitrationApi = {
  // Başvurular
  getApplications: (filters: ApplicationFilters = {}) =>
    apiClient.get('/arbitration', { params: filters }),

  getApplication: (id: string) =>
    apiClient.get(`/arbitration/${id}`),

  createApplication: (data: CreateApplicationData) =>
    apiClient.post('/arbitration', data),

  updateApplication: (id: string, data: UpdateApplicationData) =>
    apiClient.put(`/arbitration/${id}`, data),

  deleteApplication: (id: string) =>
    apiClient.delete(`/arbitration/${id}`),

  // Arabulucu atama ve durum yönetimi
  assignMediator: (id: string, mediatorId: string) =>
    apiClient.put(`/arbitration/${id}/assign-mediator`, { mediator_id: mediatorId }),

  changeStatus: (id: string, status: string, note?: string) =>
    apiClient.put(`/arbitration/${id}/change-status`, { status, note }),

  // Belgeler
  uploadDocument: (id: string, file: File, documentType: string, title?: string, isPublic = false) => {
    const formData = new FormData();
    formData.append('file', file);
    formData.append('document_type', documentType);
    if (title) formData.append('title', title);
    formData.append('is_public', isPublic.toString());

    return apiClient.post(`/arbitration/${id}/documents`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });
  },

  getDocuments: (id: string, publicOnly = false) =>
    apiClient.get(`/arbitration/${id}/documents`, { params: { public_only: publicOnly } }),

  // Zaman çizelgesi
  getTimeline: (id: string, limit = 50) =>
    apiClient.get(`/arbitration/${id}/timeline`, { params: { limit } }),

  // İstatistikler
  getStatistics: () =>
    apiClient.get('/arbitration/statistics'),
};

// Yardımcı fonksiyonlar
export const getApplicationStatusColor = (status: string): string => {
  const colors: Record<string, string> = {
    pending: 'yellow',
    accepted: 'blue',
    rejected: 'red',
    in_progress: 'purple',
    completed: 'green',
    cancelled: 'gray',
  };
  return colors[status] || 'gray';
};

export const getApplicationTypeColor = (type: string): string => {
  const colors: Record<string, string> = {
    ihtiyati: 'orange',
    ihtiyati_tedbir: 'red',
    ticari: 'blue',
    is_hukuku: 'purple',
    tuketici: 'green',
    icra: 'yellow',
    diger: 'gray',
  };
  return colors[type] || 'gray';
};

export const formatCurrency = (amount: number, currency = 'TRY'): string => {
  return new Intl.NumberFormat('tr-TR', {
    style: 'currency',
    currency,
  }).format(amount);
};

export const documentTypeOptions = [
  { value: 'basvuru_dilekcesi', label: 'Başvuru Dilekçesi' },
  { value: 'delil', label: 'Delil' },
  { value: 'vekaletname', label: 'Vekaletname' },
  { value: 'kimlik', label: 'Kimlik' },
  { value: 'sirket_belgesi', label: 'Şirket Belgesi' },
  { value: 'vergi_borcu_yoktur', label: 'Vergi Borcu Yoktur Yazısı' },
  { value: 'adres_kaydi', label: 'Adres Kaydı' },
  { value: 'diger', label: 'Diğer' },
];

export const applicationTypeOptions = [
  { value: 'ihtiyati', label: 'İhtiyati' },
  { value: 'ihtiyati_tedbir', label: 'İhtiyati Tedbir' },
  { value: 'ticari', label: 'Ticari' },
  { value: 'is_hukuku', label: 'İş Hukuku' },
  { value: 'tuketici', label: 'Tüketici' },
  { value: 'icra', label: 'İcra' },
  { value: 'diger', label: 'Diğer' },
];

export const statusOptions = [
  { value: 'pending', label: 'Beklemede' },
  { value: 'accepted', label: 'Kabul Edildi' },
  { value: 'rejected', label: 'Reddedildi' },
  { value: 'in_progress', label: 'İşlemde' },
  { value: 'completed', label: 'Tamamlandı' },
  { value: 'cancelled', label: 'İptal Edildi' },
];
