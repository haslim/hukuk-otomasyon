import React, { useState } from 'react';
import { invoicesApi, Invoice, InvoiceStats } from '../../api/modules/invoices';
import { useAsyncData } from '../../hooks/useAsyncData';
import { InvoiceList } from './components/InvoiceList';
import { InvoiceStatsCards } from './components/InvoiceStatsCards';
import { InvoiceFilters } from './components/InvoiceFilters';
import { NewInvoiceModal } from './components/NewInvoiceModal';
import { InvoiceDetailModal } from './components/InvoiceDetailModal';

export const InvoicesPage = () => {
  const [filters, setFilters] = useState({
    client_id: '',
    case_id: '',
    status: '',
    date_from: '',
    date_to: '',
    search: ''
  });
  
  const [showNewModal, setShowNewModal] = useState(false);
  const [selectedInvoice, setSelectedInvoice] = useState<Invoice | null>(null);
  const [showDetailModal, setShowDetailModal] = useState(false);

  // İstatistikleri çek
  const {
    data: stats,
    isLoading: statsLoading,
    refetch: refetchStats
  } = useAsyncData<InvoiceStats>(['invoice-stats'], invoicesApi.getStats);

  // Faturaları çek
  const {
    data: invoices,
    isLoading: invoicesLoading,
    refetch: refetchInvoices
  } = useAsyncData<Invoice[]>(
    ['invoices', filters],
    () => invoicesApi.index(filters)
  );

  const handleFilterChange = (newFilters: Partial<typeof filters>) => {
    setFilters(prev => ({ ...prev, ...newFilters }));
  };

  const handleInvoiceCreated = () => {
    setShowNewModal(false);
    refetchInvoices();
    refetchStats();
  };

  const handleInvoiceUpdated = () => {
    setShowDetailModal(false);
    setSelectedInvoice(null);
    refetchInvoices();
    refetchStats();
  };

  const handleInvoiceClick = (invoice: Invoice) => {
    setSelectedInvoice(invoice);
    setShowDetailModal(true);
  };

  return (
    <div className="space-y-6">
      {/* Header */}
      <header className="flex justify-between items-start">
        <div>
          <h1 className="text-3xl font-bold text-gray-900">Fatura Yönetimi</h1>
          <p className="text-gray-600 mt-1">Faturalarınızı oluşturun, yönetin ve takip edin</p>
        </div>
        
        <button
          onClick={() => setShowNewModal(true)}
          className="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-white font-medium hover:bg-primary/90 transition-colors"
        >
          <span className="material-symbols-outlined">add</span>
          Yeni Fatura
        </button>
      </header>

      {/* İstatistik Kartları */}
      <InvoiceStatsCards stats={stats} isLoading={statsLoading} />

      {/* Filtreler */}
      <InvoiceFilters
        filters={filters}
        onFilterChange={handleFilterChange}
      />

      {/* Fatura Listesi */}
      <InvoiceList
        invoices={invoices || []}
        isLoading={invoicesLoading}
        onInvoiceClick={handleInvoiceClick}
        onRefresh={() => {
          refetchInvoices();
          refetchStats();
        }}
      />

      {/* Yeni Fatura Modal */}
      {showNewModal && (
        <NewInvoiceModal
          isOpen={showNewModal}
          onClose={() => setShowNewModal(false)}
          onSuccess={handleInvoiceCreated}
        />
      )}

      {/* Fatura Detay Modal */}
      {showDetailModal && selectedInvoice && (
        <InvoiceDetailModal
          isOpen={showDetailModal}
          invoice={selectedInvoice}
          onClose={() => {
            setShowDetailModal(false);
            setSelectedInvoice(null);
          }}
          onUpdate={handleInvoiceUpdated}
        />
      )}
    </div>
  );
};
