import React from 'react';
import { Invoice } from '../../../api/modules/invoices';

interface InvoiceListProps {
  invoices: Invoice[];
  isLoading: boolean;
  onInvoiceClick: (invoice: Invoice) => void;
  onRefresh: () => void;
}

export const InvoiceList: React.FC<InvoiceListProps> = ({ 
  invoices, 
  isLoading, 
  onInvoiceClick, 
  onRefresh 
}) => {
  const getStatusColor = (status: string) => {
    switch (status) {
      case 'draft':
        return 'bg-gray-100 text-gray-800';
      case 'sent':
        return 'bg-orange-100 text-orange-800';
      case 'paid':
        return 'bg-green-100 text-green-800';
      case 'overdue':
        return 'bg-red-100 text-red-800';
      case 'cancelled':
        return 'bg-red-100 text-red-800';
      default:
        return 'bg-gray-100 text-gray-800';
    }
  };

  const getStatusLabel = (status: string) => {
    switch (status) {
      case 'draft':
        return 'Taslak';
      case 'sent':
        return 'Gönderilmiş';
      case 'paid':
        return 'Ödenmiş';
      case 'overdue':
        return 'Gecikmiş';
      case 'cancelled':
        return 'İptal Edilmiş';
      default:
        return status;
    }
  };

  const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('tr-TR');
  };

  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('tr-TR', {
      style: 'currency',
      currency: 'TRY'
    }).format(amount);
  };

  if (isLoading) {
    return (
      <div className="bg-white rounded-lg shadow-sm border border-gray-100">
        <div className="p-6">
          <div className="animate-pulse space-y-4">
            {[...Array(5)].map((_, index) => (
              <div key={index} className="flex items-center space-x-4">
                <div className="h-12 bg-gray-200 rounded w-24"></div>
                <div className="h-12 bg-gray-200 rounded w-32"></div>
                <div className="h-12 bg-gray-200 rounded w-48"></div>
                <div className="h-12 bg-gray-200 rounded w-24"></div>
                <div className="h-12 bg-gray-200 rounded w-20"></div>
              </div>
            ))}
          </div>
        </div>
      </div>
    );
  }

  if (invoices.length === 0) {
    return (
      <div className="bg-white rounded-lg shadow-sm border border-gray-100 p-12 text-center">
        <div className="flex flex-col items-center space-y-4">
          <div className="p-4 bg-gray-100 rounded-full">
            <span className="material-symbols-outlined text-4xl text-gray-400">receipt</span>
          </div>
          <div>
            <h3 className="text-lg font-medium text-gray-900 mb-1">Henüz fatura bulunmuyor</h3>
            <p className="text-gray-600">İlk faturanızı oluşturmak için "Yeni Fatura" butonuna tıklayın.</p>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="bg-white rounded-lg shadow-sm border border-gray-100">
      {/* Header */}
      <div className="px-6 py-4 border-b border-gray-200">
        <div className="flex items-center justify-between">
          <h2 className="text-lg font-semibold text-gray-900">
            Faturalar ({invoices.length})
          </h2>
          <button
            onClick={onRefresh}
            className="inline-flex items-center gap-2 px-3 py-2 text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors"
          >
            <span className="material-symbols-outlined text-sm">refresh</span>
            Yenile
          </button>
        </div>
      </div>

      {/* Table */}
      <div className="overflow-x-auto">
        <table className="w-full">
          <thead className="bg-gray-50 border-b border-gray-200">
            <tr>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Fatura No
              </th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Müşteri
              </th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Tarih
              </th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Vade
              </th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Tutar
              </th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Durum
              </th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                İşlemler
              </th>
            </tr>
          </thead>
          <tbody className="bg-white divide-y divide-gray-200">
            {invoices.map((invoice) => (
              <tr key={invoice.id} className="hover:bg-gray-50 cursor-pointer transition-colors">
                <td 
                  className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"
                  onClick={() => onInvoiceClick(invoice)}
                >
                  {invoice.invoice_number}
                </td>
                <td 
                  className="px-6 py-4 whitespace-nowrap text-sm text-gray-900"
                  onClick={() => onInvoiceClick(invoice)}
                >
                  {invoice.client?.name || `ID: ${invoice.client_id}`}
                </td>
                <td 
                  className="px-6 py-4 whitespace-nowrap text-sm text-gray-600"
                  onClick={() => onInvoiceClick(invoice)}
                >
                  {formatDate(invoice.issue_date)}
                </td>
                <td 
                  className="px-6 py-4 whitespace-nowrap text-sm text-gray-600"
                  onClick={() => onInvoiceClick(invoice)}
                >
                  {formatDate(invoice.due_date)}
                </td>
                <td 
                  className="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900"
                  onClick={() => onInvoiceClick(invoice)}
                >
                  {formatCurrency(invoice.total_amount)}
                </td>
                <td 
                  className="px-6 py-4 whitespace-nowrap"
                  onClick={() => onInvoiceClick(invoice)}
                >
                  <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${getStatusColor(invoice.status)}`}>
                    {getStatusLabel(invoice.status)}
                  </span>
                </td>
                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                  <div className="flex items-center space-x-2">
                    <button
                      onClick={() => onInvoiceClick(invoice)}
                      className="text-primary hover:text-primary/80 transition-colors"
                      title="Görüntüle"
                    >
                      <span className="material-symbols-outlined text-lg">visibility</span>
                    </button>
                    <button
                      onClick={(e) => {
                        e.stopPropagation();
                        // PDF indir fonksiyonu çağrılacak
                        console.log('PDF indir:', invoice.id);
                      }}
                      className="text-gray-600 hover:text-gray-800 transition-colors"
                      title="PDF İndir"
                    >
                      <span className="material-symbols-outlined text-lg">download</span>
                    </button>
                    {invoice.status === 'draft' && (
                      <button
                        onClick={(e) => {
                          e.stopPropagation();
                          // E-posta gönder fonksiyonu çağrılacak
                          console.log('E-posta gönder:', invoice.id);
                        }}
                        className="text-blue-600 hover:text-blue-800 transition-colors"
                        title="E-posta Gönder"
                      >
                        <span className="material-symbols-outlined text-lg">send</span>
                      </button>
                    )}
                  </div>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
};
