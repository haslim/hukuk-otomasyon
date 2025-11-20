import React, { useState } from 'react';
import { invoicesApi, InvoicePayment } from '../../../api/modules/invoices';

interface AddPaymentModalProps {
  isOpen: boolean;
  onClose: () => void;
  onSuccess: () => void;
  invoiceId: string;
  remainingAmount: number;
}

export const AddPaymentModal: React.FC<AddPaymentModalProps> = ({ 
  isOpen, 
  onClose, 
  onSuccess, 
  invoiceId,
  remainingAmount 
}) => {
  const [formData, setFormData] = useState({
    amount: 0,
    payment_date: new Date().toISOString().split('T')[0],
    payment_method: 'bank_transfer' as const,
    payment_reference: '',
    notes: ''
  });

  const [isSubmitting, setIsSubmitting] = useState(false);
  const [errors, setErrors] = useState<Record<string, string>>({});

  const paymentMethods = [
    { value: 'cash', label: 'Nakit' },
    { value: 'bank_transfer', label: 'Havale/EFT' },
    { value: 'credit_card', label: 'Kredi Kartı' },
    { value: 'check', label: 'Çek' }
  ];

  const handleInputChange = (field: string, value: any) => {
    setFormData((prev: any) => ({ ...prev, [field]: value }));
    
    // Hata temizleme
    if (errors[field]) {
      setErrors((prev: any) => ({ ...prev, [field]: '' }));
    }
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    
    // Validasyon
    const newErrors: Record<string, string> = {};
    
    if (!formData.amount || formData.amount <= 0) {
      newErrors.amount = 'Ödeme tutarı 0\'dan büyük olmalıdır';
    }
    
    if (formData.amount > remainingAmount) {
      newErrors.amount = `Ödeme tutarı kalan tutarı (${remainingAmount.toLocaleString('tr-TR')} ₺) geçemez`;
    }
    
    if (!formData.payment_date) {
      newErrors.payment_date = 'Ödeme tarihi seçmelisiniz';
    }

    if (Object.keys(newErrors).length > 0) {
      setErrors(newErrors);
      return;
    }

    setIsSubmitting(true);
    setErrors({});

    try {
      await invoicesApi.addPayment({
        invoice_id: invoiceId,
        amount: formData.amount,
        payment_date: formData.payment_date,
        payment_method: formData.payment_method,
        payment_reference: formData.payment_reference,
        notes: formData.notes
      });
      onSuccess();
      onClose();
      // Formu sıfırla
      setFormData({
        amount: 0,
        payment_date: new Date().toISOString().split('T')[0],
        payment_method: 'bank_transfer' as const,
        payment_reference: '',
        notes: ''
      });
    } catch (error: any) {
      setErrors({ 
        general: error.response?.data?.error || 'Ödeme eklenemedi' 
      });
    } finally {
      setIsSubmitting(false);
    }
  };

  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('tr-TR', {
      style: 'currency',
      currency: 'TRY'
    }).format(amount);
  };

  if (!isOpen) return null;

  return (
    <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div className="bg-white rounded-lg w-full max-w-md max-h-[90vh] overflow-y-auto">
        {/* Header */}
        <div className="px-6 py-4 border-b border-gray-200">
          <div className="flex items-center justify-between">
            <h2 className="text-xl font-semibold text-gray-900">Ödeme Ekle</h2>
            <button
              onClick={onClose}
              className="text-gray-400 hover:text-gray-600 transition-colors"
            >
              <span className="material-symbols-outlined text-2xl">close</span>
            </button>
          </div>
        </div>

        {/* Form */}
        <form onSubmit={handleSubmit} className="p-6">
          {errors.general && (
            <div className="mb-4 p-3 bg-red-100 border border-red-300 rounded-lg text-red-700">
              {errors.general}
            </div>
          )}

          {/* Kalan Tutar Bilgisi */}
          <div className="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <div className="flex justify-between items-center">
              <span className="text-sm text-blue-700 font-medium">Kalan Tutar:</span>
              <span className="text-lg font-bold text-blue-900">
                {formatCurrency(remainingAmount)}
              </span>
            </div>
          </div>

          {/* Ödeme Tutarı */}
          <div className="mb-4">
            <label className="block text-sm font-medium text-gray-700 mb-1">
              Ödeme Tutarı *
            </label>
            <div className="relative">
              <input
                type="number"
                min="0"
                step="0.01"
                max={remainingAmount}
                value={formData.amount}
                onChange={(e) => handleInputChange('amount', parseFloat(e.target.value))}
                className={`w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-primary focus:border-primary ${
                  errors.amount ? 'border-red-500' : 'border-gray-300'
                }`}
                placeholder="0.00"
                required
              />
              <span className="absolute right-3 top-2.5 text-gray-500">₺</span>
            </div>
            {errors.amount && (
              <p className="mt-1 text-sm text-red-600">{errors.amount}</p>
            )}
          </div>

          {/* Ödeme Tarihi */}
          <div className="mb-4">
            <label className="block text-sm font-medium text-gray-700 mb-1">
              Ödeme Tarihi *
            </label>
            <input
              type="date"
              value={formData.payment_date}
              onChange={(e) => handleInputChange('payment_date', e.target.value)}
              className={`w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-primary focus:border-primary ${
                errors.payment_date ? 'border-red-500' : 'border-gray-300'
              }`}
              required
            />
            {errors.payment_date && (
              <p className="mt-1 text-sm text-red-600">{errors.payment_date}</p>
            )}
          </div>

          {/* Ödeme Şekli */}
          <div className="mb-4">
            <label className="block text-sm font-medium text-gray-700 mb-1">
              Ödeme Şekli *
            </label>
            <select
              value={formData.payment_method}
              onChange={(e) => handleInputChange('payment_method', e.target.value)}
              className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
            >
              {paymentMethods.map(method => (
                <option key={method.value} value={method.value}>
                  {method.label}
                </option>
              ))}
            </select>
          </div>

          {/* Referans No */}
          <div className="mb-4">
            <label className="block text-sm font-medium text-gray-700 mb-1">
              Referans No
            </label>
            <input
              type="text"
              value={formData.payment_reference || ''}
              onChange={(e) => handleInputChange('payment_reference', e.target.value)}
              className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
              placeholder="İşlem referans numarası"
            />
          </div>

          {/* Notlar */}
          <div className="mb-6">
            <label className="block text-sm font-medium text-gray-700 mb-1">
              Notlar
            </label>
            <textarea
              value={formData.notes || ''}
              onChange={(e) => handleInputChange('notes', e.target.value)}
              rows={3}
              className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
              placeholder="Ödeme ile ilgili notlar..."
            />
          </div>

          {/* Butonlar */}
          <div className="flex justify-end space-x-3">
            <button
              type="button"
              onClick={onClose}
              className="px-4 py-2 text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors"
              disabled={isSubmitting}
            >
              İptal
            </button>
            <button
              type="submit"
              className="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
              disabled={isSubmitting}
            >
              {isSubmitting ? 'Kaydediliyor...' : 'Ödemeyi Ekle'}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
};
