import React from 'react';

interface InvoiceFiltersProps {
  filters: {
    client_id: string;
    case_id: string;
    status: string;
    date_from: string;
    date_to: string;
    search: string;
  };
  onFilterChange: (filters: Partial<{
    client_id: string;
    case_id: string;
    status: string;
    date_from: string;
    date_to: string;
    search: string;
  }>) => void;
}

export const InvoiceFilters: React.FC<InvoiceFiltersProps> = ({ filters, onFilterChange }) => {
  const statusOptions = [
    { value: '', label: 'Tüm Durumlar' },
    { value: 'draft', label: 'Taslak' },
    { value: 'sent', label: 'Gönderilmiş' },
    { value: 'paid', label: 'Ödenmiş' },
    { value: 'overdue', label: 'Gecikmiş' },
    { value: 'cancelled', label: 'İptal Edilmiş' }
  ];

  const handleInputChange = (field: string, value: string) => {
    onFilterChange({ [field]: value });
  };

  const handleClearFilters = () => {
    onFilterChange({
      client_id: '',
      case_id: '',
      status: '',
      date_from: '',
      date_to: '',
      search: ''
    });
  };

  const hasActiveFilters = Object.values(filters).some(value => value !== '');

  return (
    <div className="bg-white rounded-lg p-4 shadow-sm border border-gray-100">
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
        {/* Arama */}
        <div className="lg:col-span-2">
          <label className="block text-sm font-medium text-gray-700 mb-1">
            Ara
          </label>
          <div className="relative">
            <input
              type="text"
              placeholder="Fatura no, müşteri adı..."
              value={filters.search}
              onChange={(e) => handleInputChange('search', e.target.value)}
              className="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
            />
            <span className="material-symbols-outlined absolute left-3 top-2.5 text-gray-400">
              search
            </span>
          </div>
        </div>

        {/* Durum */}
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-1">
            Durum
          </label>
          <select
            value={filters.status}
            onChange={(e) => handleInputChange('status', e.target.value)}
            className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
          >
            {statusOptions.map(option => (
              <option key={option.value} value={option.value}>
                {option.label}
              </option>
            ))}
          </select>
        </div>

        {/* Müşteri ID */}
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-1">
            Müşteri ID
          </label>
          <input
            type="text"
            placeholder="Müşteri ID"
            value={filters.client_id}
            onChange={(e) => handleInputChange('client_id', e.target.value)}
            className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
          />
        </div>

        {/* Başlangıç Tarihi */}
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-1">
            Başlangıç
          </label>
          <input
            type="date"
            value={filters.date_from}
            onChange={(e) => handleInputChange('date_from', e.target.value)}
            className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
          />
        </div>

        {/* Bitiş Tarihi */}
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-1">
            Bitiş
          </label>
          <input
            type="date"
            value={filters.date_to}
            onChange={(e) => handleInputChange('date_to', e.target.value)}
            className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
          />
        </div>
      </div>

      {/* Temizle Butonu */}
      {hasActiveFilters && (
        <div className="mt-4 flex justify-end">
          <button
            onClick={handleClearFilters}
            className="inline-flex items-center gap-2 px-4 py-2 text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors"
          >
            <span className="material-symbols-outlined text-sm">clear</span>
            Filtreleri Temizle
          </button>
        </div>
      )}
    </div>
  );
};
