import React, { useState } from 'react';
import { invoicesApi, InvoiceRequest, InvoiceItem } from '../../../api/modules/invoices';
import { ClientApi } from '../../../api/modules/clients';

interface NewInvoiceModalProps {
  isOpen: boolean;
  onClose: () => void;
  onSuccess: () => void;
}

export const NewInvoiceModal: React.FC<NewInvoiceModalProps> = ({ 
  isOpen, 
  onClose, 
  onSuccess 
}) => {
  const [formData, setFormData] = useState<InvoiceRequest>({
    client_id: '',
    case_id: '',
    issue_date: new Date().toISOString().split('T')[0],
    due_date: new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
    status: 'draft',
    subtotal: 0,
    vat_rate: 18,
    vat_amount: 0,
    total_amount: 0,
    notes: '',
    items: []
  });

  const [items, setItems] = useState<Partial<InvoiceItem>[]>([
    {
      item_type: 'fee',
      description: '',
      quantity: 1,
      unit_price: 0,
      vat_rate: 18,
      line_total: 0,
      vat_amount: 0
    }
  ]);

  const [isSubmitting, setIsSubmitting] = useState(false);
  const [errors, setErrors] = useState<Record<string, string>>({});

  const calculateItemTotal = (item: Partial<InvoiceItem>) => {
    const lineTotal = (item.quantity || 0) * (item.unit_price || 0);
    const vatAmount = lineTotal * ((item.vat_rate || 0) / 100);
    return { lineTotal, vatAmount };
  };

  const updateItem = (index: number, field: string, value: any) => {
    const newItems = [...items];
    newItems[index] = { ...newItems[index], [field]: value };
    
    if (field === 'quantity' || field === 'unit_price' || field === 'vat_rate') {
      const { lineTotal, vatAmount } = calculateItemTotal(newItems[index]);
      newItems[index].line_total = lineTotal;
      newItems[index].vat_amount = vatAmount;
    }
    
    setItems(newItems);
    calculateTotals(newItems as InvoiceItem[]);
  };

  const addItem = () => {
    setItems([...items, {
      item_type: 'fee',
      description: '',
      quantity: 1,
      unit_price: 0,
      vat_rate: 18,
      line_total: 0,
      vat_amount: 0
    }]);
  };

  const removeItem = (index: number) => {
    if (items.length > 1) {
      const newItems = items.filter((_, i) => i !== index);
      setItems(newItems);
      calculateTotals(newItems as InvoiceItem[]);
    }
  };

  const calculateTotals = (itemList: InvoiceItem[]) => {
    const subtotal = itemList.reduce((sum, item) => sum + (item.line_total || 0), 0);
    const vatAmount = itemList.reduce((sum, item) => sum + (item.vat_amount || 0), 0);
    const totalAmount = subtotal + vatAmount;

    setFormData(prev => ({
      ...prev,
      subtotal,
      vat_amount: vatAmount,
      total_amount: totalAmount,
      items: itemList
    }));
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    
    if (!formData.client_id) {
      setErrors({ general: 'Müşteri seçmelisiniz' });
      return;
    }

    if (items.some(item => !item.description || !item.unit_price)) {
      setErrors({ general: 'Tüm kalem alanlarını doldurun' });
      return;
    }

    setIsSubmitting(true);
    setErrors({});

    try {
      await invoicesApi.store(formData);
      onSuccess();
      onClose();
      // Formu sıfırla
      setFormData({
        client_id: '',
        case_id: '',
        issue_date: new Date().toISOString().split('T')[0],
        due_date: new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
        status: 'draft',
        subtotal: 0,
        vat_rate: 18,
        vat_amount: 0,
        total_amount: 0,
        notes: '',
        items: []
      });
      setItems([{
        item_type: 'fee',
        description: '',
        quantity: 1,
        unit_price: 0,
        vat_rate: 18,
        line_total: 0,
        vat_amount: 0
      }]);
    } catch (error: any) {
      setErrors({ 
        general: error.response?.data?.error || 'Fatura oluşturulamadı' 
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
      <div className="bg-white rounded-lg w-full max-w-4xl max-h-[90vh] overflow-y-auto">
        {/* Header */}
        <div className="px-6 py-4 border-b border-gray-200">
          <div className="flex items-center justify-between">
            <h2 className="text-xl font-semibold text-gray-900">Yeni Fatura</h2>
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

          {/* Müşteri Bilgileri */}
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">
                Müşteri ID *
              </label>
              <input
                type="text"
                value={formData.client_id}
                onChange={(e) => setFormData(prev => ({ ...prev, client_id: e.target.value }))}
                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
                placeholder="Müşteri ID"
                required
              />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">
                Dosya ID
              </label>
              <input
                type="text"
                value={formData.case_id}
                onChange={(e) => setFormData(prev => ({ ...prev, case_id: e.target.value }))}
                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
                placeholder="Dosya ID (opsiyonel)"
              />
            </div>
          </div>

          {/* Tarih Bilgileri */}
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">
                Düzenleme Tarihi
              </label>
              <input
                type="date"
                value={formData.issue_date}
                onChange={(e) => setFormData(prev => ({ ...prev, issue_date: e.target.value }))}
                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
              />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">
                Vade Tarihi
              </label>
              <input
                type="date"
                value={formData.due_date}
                onChange={(e) => setFormData(prev => ({ ...prev, due_date: e.target.value }))}
                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
              />
            </div>
          </div>

          {/* Fatura Kalemleri */}
          <div className="mb-6">
            <div className="flex items-center justify-between mb-4">
              <h3 className="text-lg font-medium text-gray-900">Fatura Kalemleri</h3>
              <button
                type="button"
                onClick={addItem}
                className="inline-flex items-center gap-2 px-3 py-2 text-primary bg-primary/10 rounded-lg hover:bg-primary/20 transition-colors"
              >
                <span className="material-symbols-outlined text-sm">add</span>
                Kalem Ekle
              </button>
            </div>

            <div className="space-y-4">
              {items.map((item, index) => (
                <div key={index} className="border border-gray-200 rounded-lg p-4">
                  <div className="grid grid-cols-1 md:grid-cols-6 gap-4 items-end">
                    <div className="md:col-span-2">
                      <label className="block text-sm font-medium text-gray-700 mb-1">
                        Açıklama *
                      </label>
                      <input
                        type="text"
                        value={item.description || ''}
                        onChange={(e) => updateItem(index, 'description', e.target.value)}
                        className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
                        placeholder="Hizmet/ürün açıklaması"
                        required
                      />
                    </div>
                    <div>
                      <label className="block text-sm font-medium text-gray-700 mb-1">
                        Miktar
                      </label>
                      <input
                        type="number"
                        min="1"
                        step="0.01"
                        value={item.quantity || 1}
                        onChange={(e) => updateItem(index, 'quantity', parseFloat(e.target.value))}
                        className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
                      />
                    </div>
                    <div>
                      <label className="block text-sm font-medium text-gray-700 mb-1">
                        Birim Fiyat *
                      </label>
                      <input
                        type="number"
                        min="0"
                        step="0.01"
                        value={item.unit_price || 0}
                        onChange={(e) => updateItem(index, 'unit_price', parseFloat(e.target.value))}
                        className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
                        placeholder="0.00"
                        required
                      />
                    </div>
                    <div>
                      <label className="block text-sm font-medium text-gray-700 mb-1">
                        KDV %
                      </label>
                      <input
                        type="number"
                        min="0"
                        max="100"
                        step="0.1"
                        value={item.vat_rate || 18}
                        onChange={(e) => updateItem(index, 'vat_rate', parseFloat(e.target.value))}
                        className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
                      />
                    </div>
                    <div className="flex items-center justify-between">
                      <div>
                        <p className="text-sm text-gray-600">Toplam:</p>
                        <p className="font-semibold text-gray-900">
                          {formatCurrency((item.line_total || 0) + (item.vat_amount || 0))}
                        </p>
                      </div>
                      {items.length > 1 && (
                        <button
                          type="button"
                          onClick={() => removeItem(index)}
                          className="text-red-600 hover:text-red-800 transition-colors"
                        >
                          <span className="material-symbols-outlined">delete</span>
                        </button>
                      )}
                    </div>
                  </div>
                </div>
              ))}
            </div>
          </div>

          {/* Notlar */}
          <div className="mb-6">
            <label className="block text-sm font-medium text-gray-700 mb-1">
              Notlar
            </label>
            <textarea
              value={formData.notes || ''}
              onChange={(e) => setFormData(prev => ({ ...prev, notes: e.target.value }))}
              rows={3}
              className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
              placeholder="Fatura notları..."
            />
          </div>

          {/* Toplam Özet */}
          <div className="bg-gray-50 rounded-lg p-4 mb-6">
            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div>
                <p className="text-sm text-gray-600">Ara Toplam</p>
                <p className="text-lg font-semibold text-gray-900">
                  {formatCurrency(formData.subtotal || 0)}
                </p>
              </div>
              <div>
                <p className="text-sm text-gray-600">KDV Toplam</p>
                <p className="text-lg font-semibold text-gray-900">
                  {formatCurrency(formData.vat_amount || 0)}
                </p>
              </div>
              <div>
                <p className="text-sm text-gray-600">Genel Toplam</p>
                <p className="text-xl font-bold text-primary">
                  {formatCurrency(formData.total_amount || 0)}
                </p>
              </div>
            </div>
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
              {isSubmitting ? 'Kaydediliyor...' : 'Faturayı Oluştur'}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
};
