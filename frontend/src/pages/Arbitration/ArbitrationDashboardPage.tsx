import React, { useState, useEffect } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { arbitrationApi, ArbitrationStatistics } from '@/api/modules/arbitration';
import { useToast } from '@/hooks/use-toast';

const ArbitrationDashboardPage: React.FC = () => {
  const [statistics, setStatistics] = useState<ArbitrationStatistics | null>(null);
  const [loading, setLoading] = useState(true);
  const { toast } = useToast();

  useEffect(() => {
    fetchStatistics();
  }, []);

  const fetchStatistics = async () => {
    try {
      setLoading(true);
      const response = await arbitrationApi.getStatistics();
      setStatistics(response.data);
    } catch (error: any) {
      toast({
        title: 'Hata',
        description: 'İstatistikler yüklenirken hata oluştu',
        variant: 'destructive',
      });
    } finally {
      setLoading(false);
    }
  };

  if (loading) {
    return (
      <div className="flex items-center justify-center min-h-96">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
      </div>
    );
  }

  if (!statistics) {
    return (
      <div className="text-center py-8">
        <p className="text-muted-foreground">İstatistikler yüklenemedi</p>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      <h1 className="text-3xl font-bold">Arabuluculuk</h1>
      <p className="text-muted-foreground">Arabuluculuk başvuru ve süreç yönetimi</p>

      {/* Özet Kartları */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Toplam Başvuru</CardTitle>
            <div className="h-4 w-4 text-muted-foreground">📋</div>
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">{statistics.total}</div>
            <p className="text-xs text-muted-foreground">
              Bu ay: +{statistics.this_month}
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Bekleyen</CardTitle>
            <div className="h-4 w-4 text-yellow-600">⏳</div>
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">{statistics.pending}</div>
            <p className="text-xs text-muted-foreground">
              İşlem bekleyen başvurular
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">İşlemde</CardTitle>
            <div className="h-4 w-4 text-blue-600">🔄</div>
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">{statistics.in_progress}</div>
            <p className="text-xs text-muted-foreground">
              Süren devam eden başvurular
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Tamamlandı</CardTitle>
            <div className="h-4 w-4 text-green-600">✅</div>
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">{statistics.completed}</div>
            <p className="text-xs text-muted-foreground">
              Başarı oran: %{statistics.success_rate}
            </p>
          </CardContent>
        </Card>
      </div>

      {/* Başvuru Tipi Dağılımı */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <Card>
          <CardHeader>
            <CardTitle>Başvuru Tipine Göre Dağılım</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="space-y-4">
              {Object.entries(statistics.by_type).map(([type, count]) => (
                <div key={type} className="flex items-center justify-between">
                  <span className="text-sm font-medium capitalize">{type}</span>
                  <Badge variant="secondary">{count}</Badge>
                </div>
              ))}
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Duruma Göre Dağılım</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="space-y-4">
              {Object.entries(statistics.by_status).map(([status, count]) => (
                <div key={status} className="flex items-center justify-between">
                  <span className="text-sm font-medium capitalize">{status}</span>
                  <Badge variant="secondary">{count}</Badge>
                </div>
              ))}
            </div>
          </CardContent>
        </Card>
      </div>

      {/* Hızlı İşlemler */}
      <Card>
        <CardHeader>
          <CardTitle>Hızlı İşlemler</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
            <a
              href="/arbitration/new"
              className="flex items-center justify-center p-4 border rounded-lg hover:bg-accent transition-colors"
            >
              <div className="text-center">
                <div className="text-2xl mb-2">➕</div>
                <div className="text-sm font-medium">Yeni Başvuru</div>
              </div>
            </a>
            <a
              href="/arbitration?status=pending"
              className="flex items-center justify-center p-4 border rounded-lg hover:bg-accent transition-colors"
            >
              <div className="text-center">
                <div className="text-2xl mb-2">⏳</div>
                <div className="text-sm font-medium">Bekleyenler</div>
              </div>
            </a>
            <a
              href="/arbitration?status=in_progress"
              className="flex items-center justify-center p-4 border rounded-lg hover:bg-accent transition-colors"
            >
              <div className="text-center">
                <div className="text-2xl mb-2">🔄</div>
                <div className="text-sm font-medium">İşlemdekiler</div>
              </div>
            </a>
          </div>
        </CardContent>
      </Card>
    </div>
  );
};

export default ArbitrationDashboardPage;
