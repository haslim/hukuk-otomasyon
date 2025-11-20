<?php

namespace App\Repositories;

use App\Models\ArbitrationApplication;
use App\Models\ApplicationDocument;
use App\Models\ApplicationTimeline;
use Illuminate\Pagination\LengthAwarePaginator;
use Psr\Http\Message\UploadedFileInterface;

class ArbitrationRepository extends BaseRepository
{
    protected string $model = ArbitrationApplication::class;

    public function getAllApplications(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model::with(['createdBy', 'mediator', 'documents']);

        // Filtreler
        if (isset($filters['status'])) {
            $query->byStatus($filters['status']);
        }

        if (isset($filters['application_type'])) {
            $query->byApplicationType($filters['application_type']);
        }

        if (isset($filters['mediator_id'])) {
            $query->byMediator($filters['mediator_id']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('application_no', 'like', "%{$search}%")
                  ->orWhere('subject_matter', 'like', "%{$search}%");
            });
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('application_date', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('application_date', '<=', $filters['date_to']);
        }

        if (isset($filters['created_by'])) {
            $query->where('created_by', $filters['created_by']);
        }

        // Sıralama
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate($perPage);
    }

    public function getApplicationById(string $id): ?ArbitrationApplication
    {
        return $this->model::with(['createdBy', 'mediator', 'documents.uploadedBy', 'timeline.user'])
            ->find($id);
    }

    public function createApplication(array $data): ArbitrationApplication
    {
        $data['application_no'] = ArbitrationApplication::generateApplicationNo();
        $data['application_date'] ??= \Carbon\Carbon::now()->toDateString();
        $data['status'] = 'pending';
        $data['created_by'] = auth()->id();

        $application = $this->model::create($data);

        // Başvuru oluşturma olayını zaman çizelgesine ekle
        $application->addTimelineEvent(
            'created',
            'Arabuluculuk başvurusu oluşturuldu',
            [
                'application_no' => $application->application_no,
                'application_type' => $application->application_type,
            ],
            auth()->user()
        );

        return $application;
    }

    public function updateApplication(string $id, array $data): ?ArbitrationApplication
    {
        $application = $this->getApplicationById($id);
        
        if (!$application) {
            return null;
        }

        $oldData = $application->toArray();
        $application->update($data);

        // Güncelleme olayını zaman çizelgesine ekle
        $changedFields = [];
        foreach ($data as $key => $value) {
            if ($oldData[$key] != $value && $key !== 'updated_at') {
                $changedFields[$key] = [
                    'old' => $oldData[$key],
                    'new' => $value,
                ];
            }
        }

        if (!empty($changedFields)) {
            $application->addTimelineEvent(
                'updated',
                'Başvuru bilgileri güncellendi',
                [
                    'changed_fields' => $changedFields,
                ],
                auth()->user()
            );
        }

        return $application->refresh();
    }

    public function deleteApplication(string $id): bool
    {
        $application = $this->model::find($id);
        
        if (!$application) {
            return false;
        }

        // Silme olayını zaman çizelgesine ekle
        $application->addTimelineEvent(
            'deleted',
            'Başvuru silindi',
            [
                'application_no' => $application->application_no,
            ],
            auth()->user()
        );

        return $application->delete();
    }

    public function assignMediator(string $id, string $mediatorId): ?ArbitrationApplication
    {
        $application = $this->model::find($id);
        
        if (!$application) {
            return null;
        }

        $oldMediatorId = $application->mediator_id;
        $application->update(['mediator_id' => $mediatorId]);

        // Arabulucu atama olayını zaman çizelgesine ekle
        if ($oldMediatorId) {
            $application->addTimelineEvent(
                'mediator_changed',
                'Arabulucu değiştirildi',
                [
                    'old_mediator_id' => $oldMediatorId,
                    'new_mediator_id' => $mediatorId,
                ],
                auth()->user()
            );
        } else {
            $application->addTimelineEvent(
                'mediator_assigned',
                'Arabulucu atandı',
                [
                    'mediator_id' => $mediatorId,
                ],
                auth()->user()
            );
        }

        return $application->refresh();
    }

    public function changeStatus(string $id, string $status, ?string $note = null): ?ArbitrationApplication
    {
        $application = $this->model::find($id);
        
        if (!$application) {
            return null;
        }

        $application->changeStatus($status, auth()->user(), $note);

        return $application->refresh();
    }

    // Belge işlemleri
    public function addDocument(string $applicationId, UploadedFileInterface $file, array $data): ApplicationDocument
    {
        $application = $this->model::find($applicationId);
        
        if (!$application) {
            throw new \Exception('Başvuru bulunamadı');
        }

        // Dosyayı yükle
        $clientFileName = $file->getClientFilename() ?? 'unknown_file';
        $fileName = time() . '_' . $clientFileName;
        
        // PSR-7 dosyasını geçici dosyaya taşı
        $tempPath = sys_get_temp_dir() . '/' . $fileName;
        $file->moveTo($tempPath);
        
        // Laravel Storage kullanarak dosyayı kopyala
        $filePath = 'arbitration_documents/' . $fileName;
        \Illuminate\Support\Facades\Storage::disk('public')->put($filePath, file_get_contents($tempPath));
        
        // Geçici dosyayı temizle
        unlink($tempPath);

        $documentData = [
            'application_id' => $applicationId,
            'document_type' => $data['document_type'] ?? 'diger',
            'title' => $data['title'] ?? $clientFileName,
            'file_path' => $filePath,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getClientMediaType() ?? 'application/octet-stream',
            'is_public' => $data['is_public'] ?? false,
            'uploaded_by' => auth()->id(),
        ];

        $document = ApplicationDocument::create($documentData);

        // Belge ekleme olayını zaman çizelgesine ekle
        $application->addTimelineEvent(
            'document_added',
            'Belge eklendi: ' . $document->title,
            [
                'document_id' => $document->id,
                'document_type' => $document->document_type,
                'file_name' => $document->title,
            ],
            auth()->user()
        );

        return $document;
    }

    public function removeDocument(string $documentId): bool
    {
        $document = ApplicationDocument::find($documentId);
        
        if (!$document) {
            return false;
        }

        $application = $document->application;
        $documentTitle = $document->title;

        // Belge silme olayını zaman çizelgesine ekle
        $application->addTimelineEvent(
            'document_removed',
            'Belge kaldırıldı: ' . $documentTitle,
            [
                'document_id' => $document->id,
                'document_type' => $document->document_type,
                'file_name' => $documentTitle,
            ],
            auth()->user()
        );

        return $document->delete();
    }

    public function getApplicationDocuments(string $applicationId, bool $publicOnly = false): \Illuminate\Database\Eloquent\Collection
    {
        $query = ApplicationDocument::where('application_id', $applicationId)
            ->with('uploadedBy');

        if ($publicOnly) {
            $query->public();
        }

        return $query->recent()->get();
    }

    // Zaman çizelgesi işlemleri
    public function getApplicationTimeline(string $applicationId, int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return ApplicationTimeline::where('application_id', $applicationId)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function addTimelineEvent(string $applicationId, string $eventType, string $description, ?array $eventData = null): ApplicationTimeline
    {
        return ApplicationTimeline::create([
            'application_id' => $applicationId,
            'event_type' => $eventType,
            'description' => $description,
            'event_data' => $eventData,
            'user_id' => auth()->id(),
        ]);
    }

    // İstatistikler
    public function getStatistics(): array
    {
        $total = $this->model::count();
        $pending = $this->model::byStatus('pending')->count();
        $inProgress = $this->model::byStatus('in_progress')->count();
        $completed = $this->model::byStatus('completed')->count();
        $rejected = $this->model::byStatus('rejected')->count();

        // Bu ayki başvurular
        $thisMonth = $this->model::whereMonth('application_date', \Carbon\Carbon::now()->month)
            ->whereYear('application_date', \Carbon\Carbon::now()->year)
            ->count();

        // Geçen ayki başvurular
        $lastMonth = $this->model::whereMonth('application_date', \Carbon\Carbon::now()->subMonth()->month)
            ->whereYear('application_date', \Carbon\Carbon::now()->subMonth()->year)
            ->count();

        // Başvuru tiplerine göre dağılım
        $byType = $this->model::selectRaw('application_type, COUNT(*) as count')
            ->groupBy('application_type')
            ->get()
            ->pluck('count', 'application_type')
            ->toArray();

        // Durumlara göre dağılım
        $byStatus = $this->model::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();

        return [
            'total' => $total,
            'pending' => $pending,
            'in_progress' => $inProgress,
            'completed' => $completed,
            'rejected' => $rejected,
            'this_month' => $thisMonth,
            'last_month' => $lastMonth,
            'by_type' => $byType,
            'by_status' => $byStatus,
            'success_rate' => $total > 0 ? round(($completed / $total) * 100, 2) : 0,
        ];
    }
}
