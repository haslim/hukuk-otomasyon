import React, { useState } from 'react';
import { Invoice, InvoicePayment } from '../../../api/modules/invoices';

interface InvoiceDetailModalProps {
  isOpen: boolean;
  invoice: Invoice;
  onClose: () => void;
  onUpdate: () => void;
}

export const InvoiceDetailModal: React.FC<InvoiceDetailModalProps> = ({ 
  isOpen, 
  invoice, 
  onClose, 
  onUpdate 
}) => {
  const [activeTab, setActiveTab] = useState<'details' | 'payments' | 'edit'>('details');
  const [showAddPayment, setShowAddPayment] = useState(false);
  const [isUpdating, setIsUpdating] = useState(false);

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

  const handleStatusChange = async (newStatus: string) => {
    setIsUpdating(true);
    try {
      // API çağrısı yapılacak
      console.log('Status güncelle:', invoice.id, newStatus);
      onUpdate();
    } catch (error) {
      console.error('Status güncelleme hatası:', error);
    } finally {
      setIsUpdating(false);
    }
  };

  const handleSendInvoice = async () => {
    setIsUpdating(true);
    try {
      // API çağrısı yapılacak
      console.log('Fatura gönder:', invoice.id);
      onUpdate();
    } catch (error) {
      console.error('Fatura gönderme hatası:', error);
    } finally {
      setIsUpdating(false);
    }
  };

  const handleDownloadPdf = async () => {
    try {
      // PDF indirme API çağrısı yapılacak
      console.log('PDF indir:', invoice.id);
    } catch (error) {
      console.error('PDF indirme hatası:', error);
    }
  };

  if (!isOpen) return null;

  return (
    <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div className="bg-white rounded-lg w-full max-w-6xl max-h-[90vh] overflow-hidden">
        {/* Header */}
        <div className="px-6 py-4 border-b border-gray-200">
          <div className="flex items-center justify-between">
            <div>
              <h2 className="text-xl font-semibold text-gray-900">
                {invoice.invoice_number}
              </h2>
              <p className="text-sm text-gray-600 mt-1">
                {invoice.client?.name || `Müşteri ID: ${invoice.client_id}`}
              </p>
            </div>
            <div className="flex items-center space-x-3">
              <span className={`inline-flex px-3 py-1 text-sm font-semibold rounded-full ${getStatusColor(invoice.status)}`}>
                {getStatusLabel(invoice.status)}
              </span>
              <button
                onClick={onClose}
                className="text-gray-400 hover:text-gray-600 transition-colors"
              >
                <span className="material-symbols-outlined text-2xl">close</span>
              </button>
            </div>
          </div>
        </div>

        {/* Tabs */}
        <div className="border-b border-gray-200">
          <nav className="flex space-x-8 px-6">
            {(['details', 'payments', 'edit'] as const).map((tab) => (
              <button
                key={tab}
                onClick={() => setActiveTab(tab)}
                className={`py-4 px-1 border-b-2 font-medium text-sm transition-colors ${
                  activeTab === tab
                    ? 'border-primary text-primary'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                }`}
              >
                {tab === 'details' && 'Fatura Detayları'}
                {tab === 'payments' && 'Ödemeler'}
                {tab === 'edit' && 'Düzenle'}
              </button>
            ))}
          </nav>
        </div>

        {/* Content */}
        <div className="flex-1 overflow-y-auto">
          {activeTab === 'details' && (
            <div className="p-6">
              <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {/* Sol Taraf - Fatura Bilgileri */}
                <div className="space-y-6">
                  {/* Genel Bilgiler */}
                  <div className="bg-gray-50 rounded-lg p-4">
                    <h3 className="text-lg font-medium text-gray-900 mb-4">Genel Bilgiler</h3>
                    <div className="space-y-3">
                      <div className="flex justify-between">
                        <span className="text-gray-600">Fatura No:</span>
                        <span className="font-medium">{invoice.invoice_number}</span>
                      </div>
                      <div className="flex justify-between">
                        <span className="text-gray-600">Durum:</span>
                        <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${getStatusColor(invoice.status)}`}>
                          {getStatusLabel(invoice.status)}
                        </span>
                      </div>
                      <div className="flex justify-between">
                        <span className="text-gray-600">Düzenleme Tarihi:</span>
                        <span className="font-medium">{formatDate(invoice.issue_date)}</span>
                      </div>
                      <div className="flex justify-between">
                        <span className="text-gray-600">Vade Tarihi:</span>
                        <span className="font-medium">{formatDate(invoice.due_date)}</span>
                      </div>
                      {invoice.case_id && (
                        <div className="flex justify-between">
                          <span className="text-gray-600">Dosya ID:</span>
                          <span className="font-medium">{invoice.case_id}</span>
                        </div>
                      )}
                    </div>
                  </div>

                  {/* Müşteri Bilgileri */}
                  <div className="bg-gray-50 rounded-lg p-4">
                    <h3 className="text-lg font-medium text-gray-900 mb-4">Müşteri Bilgileri</h3>
                    <div className="space-y-3">
                      <div className="flex justify-between">
                        <span className="text-gray-600">Müşteri Adı:</span>
                        <span className="font-medium">{invoice.client?.name || '-'}</span>
                      </div>
                      <div className="flex justify-between">
                        <span className="text-gray-600">Müşteri ID:</span>
                        <span className="font-medium">{invoice.client_id}</span>
                      </div>
                    </div>
                  </div>
                </div>

                {/* Sağ Taraf - Finansal Bilgiler */}
                <div className="space-y-6">
                  {/* Finansal Özet */}
                  <div className="bg-gray-50 rounded-lg p-4">
                    <h3 className="text-lg font-medium text-gray-900 mb-4">Finansal Özet</h3>
                    <div className="space-y-3">
                      <div className="flex justify-between">
                        <span className="text-gray-600">Ara Toplam:</span>
                        <span className="font-medium">{formatCurrency(invoice.subtotal)}</span>
                      </div>
                      <div className="flex justify-between">
                        <span className="text-gray-600">KDV Oranı:</span>
                        <span className="font-medium">%{invoice.vat_rate}</span>
                      </div>
                      <div className="flex justify-between">
                        <span className="text-gray-600">KDV Tutarı:</span>
                        <span className="font-medium">{formatCurrency(invoice.vat_amount)}</span>
                      </div>
                      <div className="border-t pt-3">
                        <div className="flex justify-between">
                          <span className="text-lg font-semibold">Genel Toplam:</span>
                          <span className="text-lg font-bold text-primary">
                            {formatCurrency(invoice.total_amount)}
                          </span>
                        </div>
                      </div>
                      <div className="flex justify-between">
                        <span className="text-gray-600">Ödenen:</span>
                        <span className="font-medium text-green-600">
                          {formatCurrency(invoice.paid_amount)}
                        </span>
                      </div>
                      <div className="flex justify-between">
                        <span className="text-gray-600">Kalan:</span>
                        <span className="font-medium text-orange-600">
                          {formatCurrency(invoice.total_amount - invoice.paid_amount)}
                        </span>
                      </div>
                    </div>
                  </div>

                  {/* Notlar */}
                  {invoice.notes && (
                    <div className="bg-gray-50 rounded-lg p-4">
                      <h3 className="text-lg font-medium text-gray-900 mb-4">Notlar</h3>
                      <p className="text-gray-700">{invoice.notes}</p>
                    </div>
                  )}
                </div>
              </div>

              {/* Fatura Kalemleri */}
              <div className="mt-6">
                <h3 className="text-lg font-medium text-gray-900 mb-4">Fatura Kalemleri</h3>
                <div className="border border-gray-200 rounded-lg overflow-hidden">
                  <table className="w-full">
                    <thead className="bg-gray-50">
                      <tr>
                        <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                          Açıklama
                        </th>
                        <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                          Miktar
                        </th>
                        <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                          Birim Fiyat
                        </th>
                        <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                          KDV
                        </th>
                        <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                          Toplam
                        </th>
                      </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-200">
                      {invoice.items?.map((item, index) => (
                        <tr key={item.id || index}>
                          <td className="px-4 py-3 text-sm">{item.description}</td>
                          <td className="px-4 py-3 text-sm">{item.quantity}</td>
                          <td className="px-4 py-3 text-sm">{formatCurrency(item.unit_price)}</td>
                          <td className="px-4 py-3 text-sm">%{item.vat_rate}</td>
                          <td className="px-4 py-3 text-sm font-medium">
                            {formatCurrency(item.line_total + item.vat_amount)}
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          )}

          {activeTab === 'payments' && (
            <div className="p-6">
              <div className="flex justify-between items-center mb-6">
                <h3 className="text-lg font-medium text-gray-900">Ödeme Geçmişi</h3>
                {invoice.status !== 'paid' && (
                  <button
                    onClick={() => setShowAddPayment(true)}
                    className="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors"
                  >
                    <span className="material-symbols-outlined text-sm">add</span>
                    Ödeme Ekle
                  </button>
                )}
              </div>

              {invoice.payments && invoice.payments.length > 0 ? (
                <div className="border border-gray-200 rounded-lg overflow-hidden">
                  <table className="w-full">
                    <thead className="bg-gray-50">
                      <tr>
                        <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                          Tarih
                        </th>
                        <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                          Tutar
                        </th>
                        <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                          Ödeme Şekli
                        </th>
                        <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                          Referans
                        </th>
                        <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                          Notlar
                        </th>
                      </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-200">
                      {invoice.payments.map((payment) => (
                        <tr key={payment.id}>
                          <td className="px-4 py-3 text-sm">
                            {formatDate(payment.payment_date)}
                          </td>
                          <td className="px-4 py-3 text-sm font-medium">
                            {formatCurrency(payment.amount)}
                          </td>
                          <td className="px-4 py-3 text-sm">
                            {payment.payment_method === 'cash' && 'Nakit'}
                            {payment.payment_method === 'bank_transfer' && 'Havale'}
                            {payment.payment_method === 'credit_card' && 'Kredi Kartı'}
                            {payment.payment_method === 'check' && 'Çek'}
                          </td>
                          <td className="px-4 py-3 text-sm">
                            {payment.payment_reference || '-'}
                          </td>
                          <td className="px-4 py-3 text-sm">
                            {payment.notes || '-'}
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              ) : (
                <div className="text-center py-8">
                  <div className="p-4 bg-gray-100 rounded-full inline-block mb-4">
                    <span className="material-symbols-outlined text-2xl text-gray-400">payments</span>
                  </div>
                  <p className="text-gray-600">Henüz ödeme kaydı bulunmuyor</p>
                </div>
              )}
            </div>
          )}

          {activeTab === 'edit' && (
            <div className="p-6">
              <div className="text-center py-8">
                <span className="material-symbols-outlined text-4xl text-gray-400 mb-4">edit</span>
                <h3 className="text-lg font-medium text-gray-900 mb-2">Düzenleme Modu</h3>
                <p className="text-gray-600">Fatura düzenleme özelliği yakında eklenecektir.</p>
              </div>
            </div>
          )}
        </div>

        {/* Footer - Action Buttons */}
        <div className="px-6 py-4 border-t border-gray-200 bg-gray-50">
          <div className="flex justify-between items-center">
            <div className="flex space-x-3">
              <button
                onClick={handleDownloadPdf}
                className="inline-flex items-center gap-2 px-4 py-2 text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors"
              >
                <span className="material-symbols-outlined text-sm">download</span>
                PDF İndir
              </button>
              {invoice.status === 'draft' && (
                <button
                  onClick={handleSendInvoice}
                  disabled={isUpdating}
                  className="inline-flex items-center gap-2 px-4 py-2 text-blue-600 bg-blue-100 rounded-lg hover:bg-blue-200 transition-colors disabled:opacity-50"
                >
                  <span className="material-symbols-outlined text-sm">send</span>
                  {isUpdating ? 'Gönderiliyor...' : 'E-posta Gönder'}
                </button>
              )}
            </div>
            <div className="flex space-x-3">
              {invoice.status === 'draft' && (
                <button
                  onClick={() => handleStatusChange('sent')}
                  disabled={isUpdating}
                  className="inline-flex items-center gap-2 px-4 py-2 text-white bg-orange-600 rounded-lg hover:bg-orange-700 transition-colors disabled:opacity-50"
                >
                  <span className="material-symbols-outlined text-sm">send</span>
                  {isUpdating ? 'Güncelleniyor...' : 'Gönder'}
                </button>
              )}
              <button
                onClick={onClose}
                className="px-4 py-2 text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors"
              >
                Kapat
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};
