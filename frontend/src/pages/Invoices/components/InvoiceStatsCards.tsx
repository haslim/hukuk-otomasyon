import React from 'react';
import { InvoiceStats } from '../../../api/modules/invoices';

interface InvoiceStatsCardsProps {
  stats: InvoiceStats | null;
  isLoading: boolean;
}

export const InvoiceStatsCards: React.FC<InvoiceStatsCardsProps> = ({ 
  stats, 
  isLoading 
}) => {
  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('tr-TR', {
      style: 'currency',
      currency: 'TRY'
    }).format(amount);
  };

  const statCards = [
    {
      title: 'Toplam Faturalar',
      value: stats?.total_invoices || 0,
      icon: 'receipt_long',
      color: 'bg-blue-500',
      bgColor: 'bg-blue-50',
      textColor: 'text-blue-600'
    },
    {
      title: 'Toplam Tutar',
      value: stats?.total_amount || 0,
      icon: 'payments',
      color: 'bg-green-500',
      bgColor: 'bg-green-50',
      textColor: 'text-green-600',
      isCurrency: true
    },
    {
      title: 'Ödenen Tutar',
      value: stats?.paid_amount || 0,
      icon: 'check_circle',
      color: 'bg-emerald-500',
      bgColor: 'bg-emerald-50',
      textColor: 'text-emerald-600',
      isCurrency: true
    },
    {
      title: 'Bekleyen Tutar',
      value: (stats?.total_amount || 0) - (stats?.paid_amount || 0),
      icon: 'schedule',
      color: 'bg-orange-500',
      bgColor: 'bg-orange-50',
      textColor: 'text-orange-600',
      isCurrency: true
    },
    {
      title: 'Gecikmiş',
      value: stats?.overdue_count || 0,
      icon: 'warning',
      color: 'bg-red-500',
      bgColor: 'bg-red-50',
      textColor: 'text-red-600'
    },
    {
      title: 'Bu Ay',
      value: stats?.this_month_count || 0,
      icon: 'calendar_month',
      color: 'bg-purple-500',
      bgColor: 'bg-purple-50',
      textColor: 'text-purple-600'
    }
  ];

  if (isLoading) {
    return (
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
        {Array.from({ length: 6 }).map((_, index) => (
          <div key={index} className="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
            <div className="animate-pulse">
              <div className="flex items-center justify-between mb-4">
                <div className="h-8 w-8 bg-gray-200 rounded-lg"></div>
                <div className="h-6 w-16 bg-gray-200 rounded"></div>
              </div>
              <div className="h-8 bg-gray-200 rounded w-20"></div>
            </div>
          </div>
        ))}
      </div>
    );
  }

  return (
    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
      {statCards.map((card, index) => (
        <div 
          key={index}
          className="bg-white rounded-lg shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow"
        >
          <div className="flex items-center justify-between mb-4">
            <div className={`p-3 rounded-lg ${card.bgColor}`}>
              <span 
                className={`material-symbols-outlined text-xl ${card.textColor}`}
              >
                {card.icon}
              </span>
            </div>
            <div className={`text-xs font-medium px-2 py-1 rounded-full ${card.bgColor} ${card.textColor}`}>
              {index < 3 ? 'Tutar' : index === 3 ? 'Tutar' : 'Adet'}
            </div>
          </div>
          <div className="space-y-1">
            <div className="text-2xl font-bold text-gray-900">
              {card.isCurrency ? formatCurrency(card.value) : card.value.toLocaleString('tr-TR')}
            </div>
            <div className="text-sm text-gray-600">
              {card.title}
            </div>
          </div>
        </div>
      ))}
    </div>
  );
};
