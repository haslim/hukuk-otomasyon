import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table.tsx';
import { Button } from '@/components/ui/button.tsx';
import { Input } from '@/components/ui/input.tsx';
import { Badge } from '@/components/ui/badge.tsx';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card.tsx';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select.tsx';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu.tsx';
import { Search, Filter, Plus, MoreHorizontal, Eye, Edit, Trash2 } from 'lucide-react';
import {
  arbitrationApi,
  ArbitrationApplication,
  ApplicationFilters,
  getApplicationStatusColor,
  getApplicationTypeColor,
  applicationTypeOptions,
  statusOptions,
} from '@/api/modules/arbitration.ts';
import { useToast } from '@/hooks/use-toast.ts';

const ArbitrationListPage: React.FC = () => {
  const [applications, setApplications] = useState<ArbitrationApplication[]>([]);
  const [loading, setLoading] = useState(true);
  const [filters, setFilters] = useState<ApplicationFilters>({});
  const [pagination, setPagination] = useState({
    current_page: 1,
    last_page: 1,
    per_page: 15,
    total: 0,
  });
  const { toast } = useToast();

  const fetchApplications = async () => {
    try {
      setLoading(true);
      const response = await arbitrationApi.getApplications({
        ...filters,
        per_page: pagination.per_page,
      });

      setApplications(response.data.data);
      setPagination(response.data.pagination);
    } catch (error: any) {
      toast({
        title: 'Hata',
        description: error.response?.data?.message || 'Başvurular yüklenirken hata oluştu',
        variant: 'destructive',
      });
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchApplications();
  }, [filters, pagination.current_page]);

  const handleFilterChange = (key: keyof ApplicationFilters, value: string) => {
    setFilters(prev => ({
      ...prev,
      [key]: value || undefined,
    }));
    setPagination(prev => ({ ...prev, current_page: 1 }));
  };

  const handleSearch = (value: string) => {
    setFilters(prev => ({
      ...prev,
      search: value || undefined,
    }));
    setPagination(prev => ({ ...prev, current_page: 1 }));
  };

  const handleDeleteApplication = async (id: string, applicationNo: string) => {
    if (!window.confirm(`"${applicationNo}" başvurusunu silmek istediğinize emin misiniz?`)) {
      return;
    }

    try {
      await arbitrationApi.deleteApplication(id);
      toast({
        title: 'Başarılı',
        description: 'Başvuru başarıyla silindi',
      });
      fetchApplications();
    } catch (error: any) {
      toast({
        title: 'Hata',
        description: error.response?.data?.message || 'Başvuru silinirken hata oluştu',
        variant: 'destructive',
      });
    }
  };

  return (
    <div className="space-y-6">
      <div className="flex justify-between items-center">
        <h1 className="text-3xl font-bold">Arabuluculuk Başvuruları</h1>
        <Link to="/arbitration/new">
          <Button>
            <Plus className="mr-2 h-4 w-4" />
            Yeni Başvuru
          </Button>
        </Link>
      </div>

      {/* Filtreler */}
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center">
            <Filter className="mr-2 h-5 w-5" />
            Filtreler
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div className="space-y-2">
              <label className="text-sm font-medium">Arama</label>
              <div className="relative">
                <Search className="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
                <Input
                  placeholder="Başvuru no veya konu..."
                  value={filters.search || ''}
                  onChange={(e) => handleSearch(e.target.value)}
                  className="pl-10"
                />
              </div>
            </div>

            <div className="space-y-2">
              <label className="text-sm font-medium">Durum</label>
              <Select
                value={filters.status || ''}
                onValueChange={(value) => handleFilterChange('status', value)}
              >
                <SelectTrigger>
                  <SelectValue placeholder="Tüm Durumlar" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="">Tüm Durumlar</SelectItem>
                  {statusOptions.map((status) => (
                    <SelectItem key={status.value} value={status.value}>
                      {status.label}
                    </SelectItem>
                  ))}
                </SelectContent>
              </Select>
            </div>

            <div className="space-y-2">
              <label className="text-sm font-medium">Başvuru Tipi</label>
              <Select
                value={filters.application_type || ''}
                onValueChange={(value) => handleFilterChange('application_type', value)}
              >
                <SelectTrigger>
                  <SelectValue placeholder="Tüm Tipler" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="">Tüm Tipler</SelectItem>
                  {applicationTypeOptions.map((type) => (
                    <SelectItem key={type.value} value={type.value}>
                      {type.label}
                    </SelectItem>
                  ))}
                </SelectContent>
              </Select>
            </div>

            <div className="space-y-2">
              <label className="text-sm font-medium">Sıralama</label>
              <Select
                value={`${filters.sort_by || 'created_at'}_${filters.sort_order || 'desc'}`}
                onValueChange={(value) => {
                  const [sort_by, sort_order] = value.split('_');
                  setFilters(prev => ({
                    ...prev,
                    sort_by,
                    sort_order: sort_order as 'asc' | 'desc',
                  }));
                }}
              >
                <SelectTrigger>
                  <SelectValue placeholder="Sıralama" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="created_at_desc">Yeniden Eskiye</SelectItem>
                  <SelectItem value="created_at_asc">Eskiden Yeniye</SelectItem>
                  <SelectItem value="application_date_desc">Tarihe Göre Azalan</SelectItem>
                  <SelectItem value="application_date_asc">Tarihe Göre Artan</SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>
        </CardContent>
      </Card>

      {/* Başvuru Listesi */}
      <Card>
        <CardContent className="p-0">
          {loading ? (
            <div className="flex items-center justify-center p-8">
              <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
            </div>
          ) : applications.length === 0 ? (
            <div className="text-center p-8">
              <p className="text-muted-foreground mb-4">Henüz başvuru bulunmuyor</p>
              <Link to="/arbitration/new">
                <Button>
                  <Plus className="mr-2 h-4 w-4" />
                  İlk Başvuruyu Oluştur
                </Button>
              </Link>
            </div>
          ) : (
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Başvuru No</TableHead>
                  <TableHead>Başvuran</TableHead>
                  <TableHead>Cevaplayan</TableHead>
                  <TableHead>Tip</TableHead>
                  <TableHead>Konu</TableHead>
                  <TableHead>Durum</TableHead>
                  <TableHead>Tarih</TableHead>
                  <TableHead className="text-right">İşlemler</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {applications.map((application) => (
                  <TableRow key={application.id}>
                    <TableCell className="font-medium">
                      <Link
                        to={`/arbitration/${application.id}`}
                        className="hover:text-primary"
                      >
                        {application.application_no}
                      </Link>
                    </TableCell>
                    <TableCell>{application.applicant_name}</TableCell>
                    <TableCell>{application.respondent_name}</TableCell>
                    <TableCell>
                      <Badge
                        variant="outline"
                        className={`bg-${getApplicationTypeColor(
                          application.application_type
                        )}-100 text-${getApplicationTypeColor(
                          application.application_type
                        )}-800`}
                      >
                        {application.application_type_label}
                      </Badge>
                    </TableCell>
                    <TableCell>
                      <div className="max-w-xs truncate" title={application.subject_matter}>
                        {application.subject_matter}
                      </div>
                    </TableCell>
                    <TableCell>
                      <Badge
                        variant="outline"
                        className={`bg-${getApplicationStatusColor(
                          application.status
                        )}-100 text-${getApplicationStatusColor(
                          application.status
                        )}-800`}
                      >
                        {application.status_label}
                      </Badge>
                    </TableCell>
                    <TableCell>{application.formatted_application_date}</TableCell>
                    <TableCell className="text-right">
                      <DropdownMenu>
                        <DropdownMenuTrigger asChild>
                          <Button variant="ghost" size="sm">
                            <MoreHorizontal className="h-4 w-4" />
                          </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end">
                          <DropdownMenuItem asChild>
                            <Link to={`/arbitration/${application.id}`}>
                              <Eye className="mr-2 h-4 w-4" />
                              Görüntüle
                            </Link>
                          </DropdownMenuItem>
                          <DropdownMenuItem asChild>
                            <Link to={`/arbitration/${application.id}/edit`}>
                              <Edit className="mr-2 h-4 w-4" />
                              Düzenle
                            </Link>
                          </DropdownMenuItem>
                          <DropdownMenuItem
                            className="text-red-600"
                            onClick={() =>
                              handleDeleteApplication(
                                application.id,
                                application.application_no
                              )
                            }
                          >
                            <Trash2 className="mr-2 h-4 w-4" />
                            Sil
                          </DropdownMenuItem>
                        </DropdownMenuContent>
                      </DropdownMenu>
                    </TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>
          )}
        </CardContent>
      </Card>

      {/* Sayfalama */}
      {pagination.total > 0 && (
        <div className="flex items-center justify-between">
          <div className="text-sm text-muted-foreground">
            Toplam {pagination.total} kayıt gösteriliyor
            {pagination.per_page > 0 && 
              ` (Sayfa ${pagination.current_page}/${pagination.last_page})`
            }
          </div>
          <div className="flex space-x-2">
            <Button
              variant="outline"
              size="sm"
              disabled={pagination.current_page === 1}
              onClick={() =>
                setPagination(prev => ({
                  ...prev,
                  current_page: prev.current_page - 1,
                }))
              }
            >
              Önceki
            </Button>
            <Button
              variant="outline"
              size="sm"
              disabled={pagination.current_page === pagination.last_page}
              onClick={() =>
                setPagination(prev => ({
                  ...prev,
                  current_page: prev.current_page + 1,
                }))
              }
            >
              Sonraki
            </Button>
          </div>
        </div>
      )}
    </div>
  );
};

export default ArbitrationListPage;
