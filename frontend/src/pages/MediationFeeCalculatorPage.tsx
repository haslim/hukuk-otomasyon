import React, { useState, useEffect } from 'react';
import { mediationFeesApi, MediationFeeRequest, MediationFeeCalculation, MediationFeeTariff } from '../api/modules/mediationFees';
import { invoicesApi } from '../api/modules/invoices';
import { clientApi } from '../api/modules/clients';
import { caseApi } from '../api/modules/cases';

const MediationFeeCalculatorPage: React.FC = () => {
  const [requestData, setRequestData] = useState<MediationFeeRequest>({
    calculation_type: 'standard',
    party_count: 2,
    subject_value: 0,
    vat_rate: 18
  });

  const [result, setResult] = useState<any>(null);
  const [loading, setLoading] = useState(false);
  const [calculations, setCalculations] = useState<MediationFeeCalculation[]>([]);
  const [clients, setClients] = useState<any[]>([]);
  const [cases, setCases] = useState<any[]>([]);
  const [tariffs, setTariffs] = useState<MediationFeeTariff[]>([]);
  const [errors, setErrors] = useState<Record<string, string>>({});
  const [showHistory, setShowHistory] = useState(false);
  const [selectedCalculation, setSelectedCalculation] = useState<MediationFeeCalculation | null>(null);

  useEffect(() => {
    loadData();
  }, []);

  const loadData = async () => {
    try {
      const [clientsData, casesData, tariffsData, calculationsData] = await Promise.all([
        clientApi.index(),
        caseApi.index(),
        mediationFeesApi.tariffs(),
        mediationFeesApi.index()
      ]);
      setClients(clientsData);
      setCases(casesData);
      setTariffs(tariffsData);
      setCalculations(calculationsData);
    } catch (error) {
      console.error('Veri yükleme hatası:', error);
    }
  };

  const validateForm = (): boolean => {
    const newErrors: Record<string, string> = {};

    if (!requestData.subject_value || requestData.subject_value <= 0) {
      newErrors.subject_value = 'Değerleme konusu tutar 0\'dan büyük olmalıdır';
    }

    if (!requestData.party_count || requestData.party_count < 1) {
      newErrors.party_count = 'Taraf sayısı 1 veya daha fazla olmalıdır';
    }

    if (requestData.vat_rate && (requestData.vat_rate < 0 || requestData.vat_rate > 100)) {
      newErrors.vat_rate = 'KDV oranı 0-100 arasında olmalıdır';
    }

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleCalculate = async () => {
    if (!validateForm()) return;

    setLoading(true);
    try {
      const calculationResult = await mediationFeesApi.calculate(requestData);
      setResult(calculationResult);
    } catch (error: any) {
      console.error('Hesaplama hatası:', error);
      setErrors({ general: error.response?.data?.error || 'Hesaplama sırasında bir hata oluştu' });
    } finally {
      setLoading(false);
    }
  };

  const handleSave = async () => {
    if (!result) return;

    setLoading(true);
    try {
      await mediationFeesApi.store(requestData);
      await loadData(); // Listeyi yenile
      setResult(null);
      setErrors({});
      alert('Hesaplama başarıyla kaydedildi');
    } catch (error: any) {
      console.error('Kaydetme hatası:', error);
      setErrors({ general: error.response?.data?.error || 'Kaydetme sırasında bir hata oluştu' });
    } finally {
      setLoading(false);
    }
  };

  const handleCreateInvoice = async (calculation: MediationFeeCalculation) => {
    if (!calculation.client_id) {
      setErrors({ general: 'Fatura oluşturmak için müşteri seçmelisiniz' });
      return;
    }

    setLoading(true);
    try {
      const invoice = await mediationFeesApi.createInvoice(calculation.id, {
        client_id: calculation.client_id,
        case_id: calculation.case_id,
        issue_date: new Date().toISOString().split('T')[0],
        due_date: new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0]
      });
      alert(`Fatura başarıyla oluşturuldu: ${invoice.invoice_number}`);
    } catch (error: any) {
      console.error('Fatura oluşturma hatası:', error);
      setErrors({ general: error.response?.data?.error || 'Fatura oluşturma sırasında bir hata oluştu' });
    } finally {
      setLoading(false);
    }
  };

  const formatCurrency = (amount: number): string => {
    return new Intl.NumberFormat('tr-TR', {
      style: 'currency',
      currency: 'TRY'
    }).format(amount);
  };

  const formatDate = (dateString: string): string => {
    return new Date(dateString).toLocaleDateString('tr-TR');
  };

  const resetForm = () => {
    setRequestData({
      calculation_type: 'standard',
      party_count: 2,
      subject_value: 0,
      vat_rate: 18
    });
    setResult(null);
    setErrors({});
  };

  return (
    <div className="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
      <div className="mb-8">
        <h1 className="text-3xl font-bold text-gray-900 mb-2">Arabulucu Ücret Hesaplama</h1>
        <p className="text-gray-600">6325 sayılı Hukuk Uyuşmazlıklarında Arabuluculuk Kanunu'na göre ücret hesaplayın</p>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {/* Hesaplama Formu */}
        <div className="lg:col-span-2">
          <div className="bg-white shadow rounded-lg p-6">
            <h2 className="text-xl font-semibold mb-4">Hesaplama Formu</h2>
            
            {errors.general && (
              <div className="mb-4 p-4 bg-red-50 border border-red-200 rounded-md">
                <p className="text-red-600">{errors.general}</p>
              </div>
            )}

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Hesaplama Türü
                </label>
                <select
                  value={requestData.calculation_type}
                  onChange={(e) => setRequestData({...requestData, calculation_type: e.target.value as any})}
                  className="w-full p-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                >
                  <option value="standard">Standart Arabuluculuk</option>
                  <option value="commercial">Ticari Uyuşmazlıklar</option>
                  <option value="urgent">Acil Arabuluculuk</option>
                </select>
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Taraf Sayısı
                </label>
                <input
                  type="number"
                  min="1"
                  value={requestData.party_count}
                  onChange={(e) => setRequestData({...requestData, party_count: parseInt(e.target.value) || 1})}
                  className={`w-full p-2 border rounded-md focus:ring-blue-500 focus:border-blue-500 ${
                    errors.party_count ? 'border-red-300' : 'border-gray-300'
                  }`}
                />
                {errors.party_count && (
                  <p className="mt-1 text-sm text-red-600">{errors.party_count}</p>
                )}
              </div>

              <div className="md:col-span-2">
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Değerleme Konusu Tutar (₺)
                </label>
                <input
                  type="number"
                  min="0"
                  step="0.01"
                  value={requestData.subject_value}
                  onChange={(e) => setRequestData({...requestData, subject_value: parseFloat(e.target.value) || 0})}
                  className={`w-full p-2 border rounded-md focus:ring-blue-500 focus:border-blue-500 ${
                    errors.subject_value ? 'border-red-300' : 'border-gray-300'
                  }`}
                />
                {errors.subject_value && (
                  <p className="mt-1 text-sm text-red-600">{errors.subject_value}</p>
                )}
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  KDV Oranı (%)
                </label>
                <input
                  type="number"
                  min="0"
                  max="100"
                  step="0.1"
                  value={requestData.vat_rate}
                  onChange={(e) => setRequestData({...requestData, vat_rate: parseFloat(e.target.value) || 0})}
                  className={`w-full p-2 border rounded-md focus:ring-blue-500 focus:border-blue-500 ${
                    errors.vat_rate ? 'border-red-300' : 'border-gray-300'
                  }`}
                />
                {errors.vat_rate && (
                  <p className="mt-1 text-sm text-red-600">{errors.vat_rate}</p>
                )}
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Müşteri
                </label>
                <select
                  value={requestData.client_id || ''}
                  onChange={(e) => setRequestData({...requestData, client_id: e.target.value})}
                  className="w-full p-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                >
                  <option value="">Müşteri Seçin</option>
                  {clients.map((client) => (
                    <option key={client.id} value={client.id}>
                      {client.name}
                    </option>
                  ))}
                </select>
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Davası
                </label>
                <select
                  value={requestData.case_id || ''}
                  onChange={(e) => setRequestData({...requestData, case_id: e.target.value})}
                  className="w-full p-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                >
                  <option value="">Dava Seçin</option>
                  {cases.map((case_) => (
                    <option key={case_.id} value={case_.id}>
                      {case_.title}
                    </option>
                  ))}
                </select>
              </div>
            </div>

            <div className="mt-6 flex gap-3">
              <button
                onClick={handleCalculate}
                disabled={loading}
                className="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50"
              >
                {loading ? 'Hesaplanıyor...' : 'Hesapla'}
              </button>
              <button
                onClick={resetForm}
                className="px-6 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700"
              >
                Sıfırla
              </button>
              {result && (
                <button
                  onClick={handleSave}
                  disabled={loading}
                  className="px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 disabled:opacity-50"
                >
                  {loading ? 'Kaydediliyor...' : 'Kaydet'}
                </button>
              )}
            </div>
          </div>
        </div>

        {/* Sonuç ve Tarife Bilgileri */}
        <div className="space-y-6">
          {/* Sonuç */}
          {result && (
            <div className="bg-white shadow rounded-lg p-6">
              <h3 className="text-lg font-semibold mb-4">Hesaplama Sonuçları</h3>
              
              <div className="space-y-3">
                <div className="flex justify-between py-2 border-b">
                  <span className="font-medium">Temel Ücret:</span>
                  <span className="font-bold">{formatCurrency(result.base_fee)}</span>
                </div>
                <div className="flex justify-between py-2 border-b">
                  <span className="font-medium">KDV (%{result.vat_rate}):</span>
                  <span className="font-bold">{formatCurrency(result.vat_amount)}</span>
                </div>
                <div className="flex justify-between py-2 border-b">
                  <span className="font-medium">Toplam Ücret:</span>
                  <span className="font-bold text-lg">{formatCurrency(result.total_fee)}</span>
                </div>
                <div className="flex justify-between py-2">
                  <span className="font-medium">Taraf Başına Düşen:</span>
                  <span className="font-bold">{formatCurrency(result.fee_per_party)}</span>
                </div>
              </div>

              {result.calculation_details?.calculation_steps && (
                <div className="mt-4 p-4 bg-gray-50 rounded-md">
                  <h4 className="font-medium mb-2">Hesaplama Adımları:</h4>
                  <ol className="list-decimal list-inside space-y-1 text-sm text-gray-700">
                    {result.calculation_details.calculation_steps.map((step, index) => (
                      <li key={index}>{step}</li>
                    ))}
                  </ol>
                </div>
              )}
            </div>
          )}

          {/* Tarife Bilgileri */}
          <div className="bg-white shadow rounded-lg p-6">
            <h3 className="text-lg font-semibold mb-4">Mevcut Tarifeler</h3>
            
            <div className="space-y-4">
              {tariffs.map((tariffGroup, index) => (
                <div key={index} className="border rounded-md p-4">
                  <h4 className="font-medium mb-2">
                    {tariffGroup.type === 'standard' && 'Standart Arabuluculuk'}
                    {tariffGroup.type === 'commercial' && 'Ticari Uyuşmazlıklar'}
                    {tariffGroup.type === 'urgent' && 'Acil Arabuluculuk'}
                  </h4>
                  <div className="overflow-x-auto">
                    <table className="min-w-full text-sm">
                      <thead>
                        <tr className="bg-gray-50">
                          <th className="px-2 py-1 text-left border">Tutar Aralığı</th>
                          <th className="px-2 py-1 text-right border">Ücret</th>
                          <th className="px-2 py-1 text-left border">Uygulama</th>
                        </tr>
                      </thead>
                      <tbody>
                        {tariffGroup.tariffs.map((tariff, tIndex) => (
                          <tr key={tIndex} className="border-b">
                            <td className="px-2 py-1 border">
                              {formatCurrency(tariff.min)} - {tariff.max ? formatCurrency(tariff.max) : 'Sınırsız'}
                            </td>
                            <td className="px-2 py-1 text-right border">
                              {tariff.fee ? formatCurrency(tariff.fee) : `%${tariff.percentage}`}
                            </td>
                            <td className="px-2 py-1 border">
                              {tariff.party_rule === 'per_party' ? 'Taraf başına' : 'Toplam'}
                            </td>
                          </tr>
                        ))}
                      </tbody>
                    </table>
                  </div>
                </div>
              ))}
            </div>
          </div>
        </div>
      </div>

      {/* Hesaplama Geçmişi */}
      <div className="mt-8">
        <div className="flex justify-between items-center mb-4">
          <h2 className="text-xl font-semibold">Hesaplama Geçmişi</h2>
          <button
            onClick={() => setShowHistory(!showHistory)}
            className="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700"
          >
            {showHistory ? 'Gizle' : 'Göster'}
          </button>
        </div>

        {showHistory && (
          <div className="bg-white shadow rounded-lg overflow-hidden">
            <div className="overflow-x-auto">
              <table className="min-w-full">
                <thead className="bg-gray-50">
                  <tr>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tarih</th>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tür</th>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Taraf Sayısı</th>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tutar</th>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Toplam Ücret</th>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">İşlemler</th>
                  </tr>
                </thead>
                <tbody className="bg-white divide-y divide-gray-200">
                  {calculations.map((calc) => (
                    <tr key={calc.id}>
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {formatDate(calc.calculation_date)}
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {calc.calculation_type === 'standard' && 'Standart'}
                        {calc.calculation_type === 'commercial' && 'Ticari'}
                        {calc.calculation_type === 'urgent' && 'Acil'}
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {calc.party_count}
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {formatCurrency(calc.subject_value)}
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        {formatCurrency(calc.total_fee)}
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <button
                          onClick={() => setSelectedCalculation(calc)}
                          className="text-blue-600 hover:text-blue-900 mr-3"
                        >
                          Görüntüle
                        </button>
                        {calc.client_id && (
                          <button
                            onClick={() => handleCreateInvoice(calc)}
                            className="text-green-600 hover:text-green-900 mr-3"
                          >
                            Fatura Oluştur
                          </button>
                        )}
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
        )}
      </div>
    </div>
  );
};

export default MediationFeeCalculatorPage;
