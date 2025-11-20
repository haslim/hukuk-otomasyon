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
      <div className="bg-white rounded-lg shadow-sm border border-gray-200">
        <div className="p-6">
          <div className="animate-pulse space-y-4">
            {/* Header row skeleton */}
            <div className="flex space-x-4">
              {Array.from({ length: 6 }).map((_, i) => (
                <div key={i} className="h-4 bg-gray-200 rounded flex-1"></div>
              ))}
            </div>
            {/* Data rows skeleton */}
            {Array.from({ length: 5 }).map((_, rowIndex) => (
              <div key={rowIndex} className="flex space-x-4 mt-3">
                {Array.from({ length: 6 }).map((_, colIndex) => (
                  <div key={colIndex} className="h-8 bg-gray-200 rounded flex-1"></div>
                ))}
              </div>
            ))}
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="bg-white rounded-lg shadow-sm border border-gray-200">
      {/* Table */}
      <div className="overflow-x-auto">
        <table className="w-full">
          <thead className="bg-gray-50 border-b border-gray-200">
            <tr>
              <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                Fatura No
              </th>
              <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                Müşteri
              </th>
              <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                Tutar
              </th>
              <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                Durum
              </th>
              <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                Vade Tarihi
              </th>
              <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                İşlemler
              </th>
            </tr>
          </thead>
          <tbody className="divide-y divide-gray-200">
            {invoices.length > 0 ? (
              invoices.map((invoice) => (
                <tr 
                  key={invoice.id}
                  className="hover:bg-gray-50 cursor-pointer transition-colors"
                  onClick={() => onInvoiceClick(invoice)}
                >
                  <td className="px-4 py-3 text-sm font-medium">
                    {invoice.invoice_number}
                  </td>
                  <td className="px-4 py-3 text-sm">
                    <div>
                      <div className="font-medium">{invoice.client?.name || `Müşteri ID: ${invoice.client_id}`}</div>
                      {invoice.case_id && (
                        <div className="text-xs text-gray-500">Dosya #{invoice.case_id}</div>
                      )}
                    </div>
                  </td>
                  <td className="px-4 py-3 text-sm font-medium">
                    {formatCurrency(invoice.total_amount)}
                  </td>
                  <td className="px-4 py-3 text-sm">
                    <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${getStatusColor(invoice.status)}`}>
                      {getStatusLabel(invoice.status)}
                    </span>
                  </td>
                  <td className="px-4 py-3 text-sm">
                    {formatDate(invoice.due_date)}
                  </td>
                  <td className="px-4 py-3 text-sm">
                    <div className="flex space-x-2">
                      <button
                        onClick={(e) => {
                          e.stopPropagation();
                          // Ödeme detayı modalı açılacak
                          onInvoiceClick(invoice);
                        }}
                        className="text-blue-600 hover:text-blue-800 transition-colors"
                        title="Detayları Görüntüle"
                      >
                        <span className="material-symbols-outlined text-sm">visibility</span>
                      </button>
                      <button
                        onClick={(e) => {
                          e.stopPropagation();
                          // PDF indirme fonksiyonu
                          console.log('PDF indir:', invoice.id);
                        }}
                        className="text-gray-600 hover:text-gray-800 transition-colors"
                        title="PDF İndir"
                      >
                        <span className="material-symbols-outlined text-sm">download</span>
                      </button>
                    </div>
                  </td>
                </tr>
              ))
            ) : (
              <tr>
                <td colSpan={6} className="px-4 py-8 text-center">
                  <div className="flex flex-col items-center">
                    <span className="material-symbols-outlined text-4xl text-gray-300 mb-3">receipt_long</span>
                    <p className="text-gray-500 text-lg font-medium">Fatura bulunamadı</p>
                    <p className="text-gray-400 text-sm mt-1">
                      Belirtilen kriterlere uygun fatura kaydı bulunmamaktadır.
                    </p>
                  </div>
                </td>
              </tr>
            )}
          </tbody>
        </table>
      </div>

      {/* Footer with refresh button */}
      <div className="px-4 py-3 border-t border-gray-200 bg-gray-50">
        <div className="flex items-center justify-between">
          <div className="text-sm text-gray-600">
            {invoices.length > 0 && `Toplam ${invoices.length} fatura`}
          </div>
          <button
            onClick={onRefresh}
            className="inline-flex items-center gap-2 px-3 py-1.5 text-sm text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:border-gray-400 transition-colors"
          >
            <span className="material-symbols-outlined text-sm">refresh</span>
            Yenile
          </button>
        </div>
      </div>
    </div>
  );
};
