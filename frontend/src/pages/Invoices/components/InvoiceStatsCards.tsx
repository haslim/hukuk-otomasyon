import React from 'react';
import { InvoiceStats } from '../../../api/modules/invoices';

interface InvoiceStatsCardsProps {
  stats?: InvoiceStats;
  isLoading: boolean;
}

export const InvoiceStatsCards: React.FC<InvoiceStatsCardsProps> = ({ stats, isLoading }) => {
  if (isLoading) {
    return (
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
        {[...Array(5)].map((_, index) => (
          <div key={index} className="bg-white rounded-lg p-6 shadow-sm animate-pulse">
            <div className="h-4 bg-gray-200 rounded w-20 mb-2"></div>
            <div className="h-8 bg-gray-200 rounded w-24"></div>
          </div>
        ))}
      </div>
    );
  }

  const cards = [
    {
      title: 'Toplam Fatura',
      value: stats?.total_invoices || 0,
      bgColor: 'bg-blue-50',
      textColor: 'text-blue-600',
      icon: 'receipt'
    },
    {
      title: 'Taslak',
      value: stats?.draft_invoices || 0,
      bgColor: 'bg-gray-50',
      textColor: 'text-gray-600',
      icon: 'edit'
    },
    {
      title: 'Gönderilmiş',
      value: stats?.sent_invoices || 0,
      bgColor: 'bg-orange-50',
      textColor: 'text-orange-600',
      icon: 'send'
    },
    {
      title: 'Ödenmiş',
      value: stats?.paid_invoices || 0,
      bgColor: 'bg-green-50',
      textColor: 'text-green-600',
      icon: 'check_circle'
    },
    {
      title: 'Gecikmiş',
      value: stats?.overdue_invoices || 0,
      bgColor: 'bg-red-50',
      textColor: 'text-red-600',
      icon: 'warning'
    }
  ];

  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('tr-TR', {
      style: 'currency',
      currency: 'TRY'
    }).format(amount);
  };

  return (
    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
      {cards.map((card) => (
        <div key={card.title} className="bg-white rounded-lg p-6 shadow-sm border border-gray-100">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm font-medium text-gray-600">{card.title}</p>
              <p className={`text-2xl font-bold ${card.textColor} mt-1`}>
                {card.value}
              </p>
            </div>
            <div className={`p-3 rounded-full ${card.bgColor}`}>
              <span className="material-symbols-outlined text-xl">{card.icon}</span>
            </div>
          </div>
        </div>
      ))}
      
      {/* Toplam Tutar Kartı */}
      <div className="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg p-6 shadow-sm text-white lg:col-span-2">
        <div className="flex items-center justify-between">
          <div>
            <p className="text-blue-100 text-sm font-medium">Toplam Tutar</p>
            <p className="text-3xl font-bold mt-1">
              {formatCurrency(stats?.total_amount || 0)}
            </p>
            <p className="text-blue-100 text-sm mt-2">
              Ödenmiş: {formatCurrency(stats?.paid_amount || 0)} / 
              Kalan: {formatCurrency(stats?.unpaid_amount || 0)}
            </p>
          </div>
          <div className="p-4 bg-white/20 rounded-full">
            <span className="material-symbols-outlined text-2xl">account_balance_wallet</span>
          </div>
        </div>
      </div>
    </div>
  );
};
