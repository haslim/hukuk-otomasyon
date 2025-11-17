import { useNavigate } from 'react-router-dom';
import { DashboardApi } from '../../api/modules/dashboard';
import { useAsyncData } from '../../hooks/useAsyncData';

const activityFeed = [
  {
    icon: 'add',
    avatarBg: 'bg-green-100',
    iconColor: 'text-green-600',
    title: '"Demir v. Çelik" dosyasına yeni masraf eklendi.',
    time: '2 saat önce',
    amount: '+250 TL',
  },
  {
    icon: 'person_add',
    avatarBg: 'bg-blue-100',
    iconColor: 'text-blue-600',
    title: 'Yeni müvekkil kaydı: Ayşe Kaya',
    time: 'Dün, 14:30',
  },
  {
    icon: 'event_busy',
    avatarBg: 'bg-red-100',
    iconColor: 'text-red-600',
    title: '"Kara v. Ak" duruşması ertelendi.',
    time: '2 gün önce',
  },
];

export const DashboardPage = () => {
  const { data, isLoading } = useAsyncData(['dashboard'], DashboardApi.overview);
  const navigate = useNavigate();

  const stats = [
    {
      label: 'Bekleyen Görevler',
      value: data?.open_tasks ?? 0,
      icon: 'task_alt',
      color: 'text-orange-500',
      bg: 'bg-orange-500/20',
      trend: '+2%',
      trendDirection: 'up' as const,
      path: '/workflow',
    },
    {
      label: 'Yaklaşan Duruşmalar',
      value: data?.hearings_today ?? 0,
      icon: 'gavel',
      color: 'text-blue-500',
      bg: 'bg-blue-500/20',
      trend: '-1%',
      trendDirection: 'down' as const,
      path: '/calendar',
    },
    {
      label: 'Kritik Süreler (7 gün)',
      value: data?.upcoming_deadlines ?? 0,
      icon: 'schedule',
      color: 'text-purple-500',
      bg: 'bg-purple-500/20',
      trend: '+5%',
      trendDirection: 'up' as const,
      path: '/cases',
    },
  ];

  const cashSummary = {
    income: data?.cash_summary?.income ?? 0,
    expense: data?.cash_summary?.expense ?? 0,
  };

  if (isLoading) {
    return <p className="text-sm text-[#A0AEC0]">Veriler yükleniyor...</p>;
  }

  return (
    <section className="flex flex-col gap-8">
      <div className="flex items-center gap-2 text-sm">
        <span className="text-[#A0AEC0]">Ana Sayfa</span>
        <span className="text-[#A0AEC0]">/</span>
        <span className="text-[#1A202C] font-medium">Dashboard</span>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        {stats.map((stat) => (
          <article
            key={stat.label}
            className="flex flex-col gap-2 rounded-xl p-6 border border-[#E2E8F0] bg-white cursor-pointer hover:bg-gray-50 transition-colors"
            onClick={() => navigate(stat.path)}
          >
            <div className="flex items-center justify-between">
              <p className="text-base font-medium">{stat.label}</p>
              <span className={`size-8 rounded-lg flex items-center justify-center ${stat.bg} ${stat.color}`}>
                <span className="material-symbols-outlined text-lg">{stat.icon}</span>
              </span>
            </div>
            <p className="text-3xl font-bold">{stat.value}</p>
            <div className="flex items-center gap-1 text-sm">
              <span
                className={`material-symbols-outlined text-base ${
                  stat.trendDirection === 'up' ? 'text-green-500' : 'text-red-500'
                }`}
              >
                {stat.trendDirection === 'up' ? 'arrow_upward' : 'arrow_downward'}
              </span>
              <p
                className={`font-medium ${
                  stat.trendDirection === 'up' ? 'text-green-500' : 'text-red-500'
                }`}
              >
                {stat.trend}
              </p>
              <span className="text-[#A0AEC0]">geçen haftadan</span>
            </div>
          </article>
        ))}
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <article className="rounded-xl border border-[#E2E8F0] bg-white p-6 lg:col-span-2">
          <div className="flex items-center justify-between mb-4">
            <h3 className="text-lg font-bold">Son Aktiviteler</h3>
            <button
              type="button"
              className="text-sm font-semibold text-[#2463eb]"
              onClick={() => navigate('/notifications')}
            >
              Tümünü Gör
            </button>
          </div>
          <div className="space-y-4">
            {activityFeed.map((activity) => (
              <div
                key={activity.title}
                className="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-colors"
              >
                <span className={`size-10 rounded-full flex items-center justify-center mr-4 ${activity.avatarBg}`}>
                  <span className={`material-symbols-outlined ${activity.iconColor}`}>{activity.icon}</span>
                </span>
                <div className="flex-1">
                  <p className="text-sm font-medium">{activity.title}</p>
                  <p className="text-xs text-[#A0AEC0]">{activity.time}</p>
                </div>
                {activity.amount && (
                  <span className="text-sm font-bold text-[#1A202C]">{activity.amount}</span>
                )}
              </div>
            ))}
          </div>
        </article>
        <article
          className="rounded-xl border border-[#E2E8F0] bg-white p-6 cursor-pointer hover:bg-gray-50 transition-colors"
          onClick={() => navigate('/finance/cash')}
        >
          <h3 className="text-lg font-bold mb-4">Kasa Özeti</h3>
          <div className="space-y-4">
            <div className="rounded-lg border border-[#E2E8F0] p-4">
              <p className="text-sm text-[#A0AEC0]">Tahsilat</p>
              <p className="text-2xl font-bold text-emerald-600">
                ₺{cashSummary.income.toLocaleString('tr-TR')}
              </p>
            </div>
            <div className="rounded-lg border border-[#E2E8F0] p-4">
              <p className="text-sm text-[#A0AEC0]">Gider</p>
              <p className="text-2xl font-bold text-rose-600">
                ₺{cashSummary.expense.toLocaleString('tr-TR')}
              </p>
            </div>
          </div>
        </article>
      </div>
    </section>
  );
};

