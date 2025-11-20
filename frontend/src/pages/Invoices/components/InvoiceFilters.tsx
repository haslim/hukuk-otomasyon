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

export const InvoiceFilters: React.FC<InvoiceFiltersProps> = ({ 
  filters, 
  onFilterChange 
}) => {
  const handleInputChange = (field: string, value: string) => {
    onFilterChange({ [field]: value });
  };

  const handleReset = () => {
    onFilterChange({
      client_id: '',
      case_id: '',
      status: '',
      date_from: '',
      date_to: '',
      search: ''
    });
  };

  return (
    <div className="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
      <div className="space-y-4">
        {/* Header */}
        <div className="flex items-center justify-between mb-6">
          <h3 className="text-lg font-semibold text-gray-900">Filtreler</h3>
          <button
            onClick={handleReset}
            className="text-sm text-gray-600 hover:text-gray-800 transition-colors"
          >
            Temizle
          </button>
        </div>

        {/* Filters Grid */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4">
          {/* Müşteri ID */}
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">
              Müşteri ID
            </label>
            <input
              type="text"
              value={filters.client_id}
              onChange={(e) => handleInputChange('client_id', e.target.value)}
              className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
              placeholder="Müşteri ID..."
            />
          </div>

          {/* Dosya ID */}
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">
              Dosya ID
            </label>
            <input
              type="text"
              value={filters.case_id}
              onChange={(e) => handleInputChange('case_id', e.target.value)}
              className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
              placeholder="Dosya ID..."
            />
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
              <option value="">Tümü</option>
              <option value="draft">Taslak</option>
              <option value="sent">Gönderilmiş</option>
              <option value="paid">Ödenmiş</option>
              <option value="overdue">Gecikmiş</option>
              <option value="cancelled">İptal Edilmiş</option>
            </select>
          </div>

          {/* Başlangıç Tarihi */}
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">
              Başlangıç Tarihi
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
              Bitiş Tarihi
            </label>
            <input
              type="date"
              value={filters.date_to}
              onChange={(e) => handleInputChange('date_to', e.target.value)}
              className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
            />
          </div>
        </div>

        {/* Arama */}
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-1">
            Arama
          </label>
          <div className="relative">
            <input
              type="text"
              value={filters.search}
              onChange={(e) => handleInputChange('search', e.target.value)}
              className="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
              placeholder="Fatura no, müşteri adı..."
            />
            <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <span className="material-symbols-outlined text-gray-400 text-sm">search</span>
            </div>
          </div>
        </div>

        {/* Active Filters Display */}
        {(filters.client_id || filters.case_id || filters.status || filters.date_from || filters.date_to || filters.search) && (
          <div className="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
            <div className="flex items-center justify-between">
              <div className="text-sm text-blue-800">
                <span className="font-medium">Aktif Filtreler:</span>
                <div className="flex flex-wrap gap-2 mt-1">
                  {filters.client_id && (
                    <span className="inline-flex px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded">
                      Müşteri ID: {filters.client_id}
                    </span>
                  )}
                  {filters.case_id && (
                    <span className="inline-flex px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded">
                      Dosya ID: {filters.case_id}
                    </span>
                  )}
                  {filters.status && (
                    <span className="inline-flex px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded">
                      Durum: {filters.status === 'draft' ? 'Taslak' : 
                              filters.status === 'sent' ? 'Gönderilmiş' :
                              filters.status === 'paid' ? 'Ödenmiş' :
                              filters.status === 'overdue' ? 'Gecikmiş' :
                              filters.status === 'cancelled' ? 'İptal Edilmiş' : filters.status}
                    </span>
                  )}
                  {filters.date_from && (
                    <span className="inline-flex px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded">
                      Başlangıç: {filters.date_from}
                    </span>
                  )}
                  {filters.date_to && (
                    <span className="inline-flex px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded">
                      Bitiş: {filters.date_to}
                    </span>
                  )}
                  {filters.search && (
                    <span className="inline-flex px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded">
                      Arama: {filters.search}
                    </span>
                  )}
                </div>
              </div>
            </div>
          </div>
        )}
      </div>
    </div>
  );
};
