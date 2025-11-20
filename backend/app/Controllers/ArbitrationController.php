<?php

namespace App\Controllers;

use App\Repositories\ArbitrationRepository;
use App\Support\AuthContext;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\UploadedFileInterface;

class ArbitrationController extends Controller
{
    private ArbitrationRepository $repository;

    public function __construct(ArbitrationRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Tüm başvuruları listele
     */
    public function index(Request $request, Response $response): Response
    {
        $filters = $request->getQueryParams();
        $perPage = (int) ($filters['per_page'] ?? 15);
        
        $applications = $this->repository->getAllApplications($filters, $perPage);

        return $this->json($response, [
            'data' => $applications->items(),
            'pagination' => [
                'current_page' => $applications->currentPage(),
                'last_page' => $applications->lastPage(),
                'per_page' => $applications->perPage(),
                'total' => $applications->total(),
            ],
        ]);
    }

    /**
     * Yeni başvuru oluştur
     */
    public function store(Request $request, Response $response): Response
    {
        $user = AuthContext::user();
        if (!$user) {
            return $this->json($response, ['message' => 'Unauthorized'], 401);
        }

        $data = (array) $request->getParsedBody();

        // Validasyon
        $errors = $this->validateApplicationData($data);
        if (!empty($errors)) {
            return $this->json($response, [
                'message' => 'Validation errors',
                'errors' => $errors
            ], 422);
        }

        try {
            $application = $this->repository->createApplication($data);
            
            return $this->json($response, [
                'message' => 'Başvuru başarıyla oluşturuldu',
                'data' => $this->formatApplication($application)
            ], 201);

        } catch (\Exception $e) {
            return $this->json($response, [
                'message' => 'Başvuru oluşturulurken hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Başvuru detayı
     */
    public function show(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];
        $application = $this->repository->getApplicationById($id);

        if (!$application) {
            return $this->json($response, ['message' => 'Başvuru bulunamadı'], 404);
        }

        // Yetki kontrolü - sadece ilgili kullanıcılar görebilir
        $user = AuthContext::user();
        if (!$this->canViewApplication($application, $user)) {
            return $this->json($response, ['message' => 'Unauthorized'], 403);
        }

        return $this->json($response, [
            'data' => $this->formatApplicationDetail($application)
        ]);
    }

    /**
     * Başvuru güncelle
     */
    public function update(Request $request, Response $response, array $args): Response
    {
        $user = AuthContext::user();
        if (!$user) {
            return $this->json($response, ['message' => 'Unauthorized'], 401);
        }

        $id = $args['id'];
        $data = (array) $request->getParsedBody();

        // Yetki kontrolü
        $application = $this->repository->getApplicationById($id);
        if (!$application) {
            return $this->json($response, ['message' => 'Başvuru bulunamadı'], 404);
        }

        if (!$this->canUpdateApplication($application, $user)) {
            return $this->json($response, ['message' => 'Unauthorized'], 403);
        }

        // Validasyon
        $errors = $this->validateApplicationData($data, true);
        if (!empty($errors)) {
            return $this->json($response, [
                'message' => 'Validation errors',
                'errors' => $errors
            ], 422);
        }

        try {
            $updatedApplication = $this->repository->updateApplication($id, $data);
            
            return $this->json($response, [
                'message' => 'Başvuru başarıyla güncellendi',
                'data' => $this->formatApplication($updatedApplication)
            ]);

        } catch (\Exception $e) {
            return $this->json($response, [
                'message' => 'Başvuru güncellenirken hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Başvuru sil
     */
    public function destroy(Request $request, Response $response, array $args): Response
    {
        $user = AuthContext::user();
        if (!$user) {
            return $this->json($response, ['message' => 'Unauthorized'], 401);
        }

        $id = $args['id'];
        $application = $this->repository->getApplicationById($id);

        if (!$application) {
            return $this->json($response, ['message' => 'Başvuru bulunamadı'], 404);
        }

        // Yetki kontrolü - sadece admin veya oluşturan kişi silebilir
        if (!$this->canDeleteApplication($application, $user)) {
            return $this->json($response, ['message' => 'Unauthorized'], 403);
        }

        try {
            $success = $this->repository->deleteApplication($id);
            
            if ($success) {
                return $this->json($response, [
                    'message' => 'Başvuru başarıyla silindi'
                ]);
            } else {
                return $this->json($response, [
                    'message' => 'Başvuru silinemedi'
                ], 500);
            }

        } catch (\Exception $e) {
            return $this->json($response, [
                'message' => 'Başvuru silinirken hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Arabulucu ata
     */
    public function assignMediator(Request $request, Response $response, array $args): Response
    {
        $user = AuthContext::user();
        if (!$user) {
            return $this->json($response, ['message' => 'Unauthorized'], 401);
        }

        $id = $args['id'];
        $data = (array) $request->getParsedBody();

        if (empty($data['mediator_id'])) {
            return $this->json($response, [
                'message' => 'Arabulucu ID zorunludur'
            ], 422);
        }

        try {
            $application = $this->repository->assignMediator($id, $data['mediator_id']);
            
            return $this->json($response, [
                'message' => 'Arabulucu başarıyla atandı',
                'data' => $this->formatApplication($application)
            ]);

        } catch (\Exception $e) {
            return $this->json($response, [
                'message' => 'Arabulucu atanırken hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Durum değiştir
     */
    public function changeStatus(Request $request, Response $response, array $args): Response
    {
        $user = AuthContext::user();
        if (!$user) {
            return $this->json($response, ['message' => 'Unauthorized'], 401);
        }

        $id = $args['id'];
        $data = (array) $request->getParsedBody();

        if (empty($data['status'])) {
            return $this->json($response, [
                'message' => 'Durum zorunludur'
            ], 422);
        }

        try {
            $application = $this->repository->changeStatus(
                $id, 
                $data['status'], 
                $data['note'] ?? null
            );
            
            return $this->json($response, [
                'message' => 'Durum başarıyla değiştirildi',
                'data' => $this->formatApplication($application)
            ]);

        } catch (\Exception $e) {
            return $this->json($response, [
                'message' => 'Durum değiştirilirken hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Belge yükle
     */
    public function uploadDocument(Request $request, Response $response, array $args): Response
    {
        $user = AuthContext::user();
        if (!$user) {
            return $this->json($response, ['message' => 'Unauthorized'], 401);
        }

        $id = $args['id'];
        $uploadedFiles = $request->getUploadedFiles();
        
        if (empty($uploadedFiles['file'])) {
            return $this->json($response, [
                'message' => 'Dosya yüklenmedi'
            ], 422);
        }

        $file = $uploadedFiles['file'];
        $data = (array) $request->getParsedBody();

        try {
            $document = $this->repository->addDocument($id, $file, $data);
            
            return $this->json($response, [
                'message' => 'Belge başarıyla yüklendi',
                'data' => $this->formatDocument($document)
            ], 201);

        } catch (\Exception $e) {
            return $this->json($response, [
                'message' => 'Belge yüklenirken hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Belgeleri listele
     */
    public function getDocuments(Request $request, Response $response, array $args): Response
    {
        $user = AuthContext::user();
        if (!$user) {
            return $this->json($response, ['message' => 'Unauthorized'], 401);
        }

        $id = $args['id'];
        $publicOnly = $request->getQueryParams()['public_only'] ?? false;

        try {
            $documents = $this->repository->getApplicationDocuments($id, $publicOnly);
            
            return $this->json($response, [
                'data' => $documents->map(fn($doc) => $this->formatDocument($doc))
            ]);

        } catch (\Exception $e) {
            return $this->json($response, [
                'message' => 'Belgeler listelenirken hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Zaman çizelgesi
     */
    public function getTimeline(Request $request, Response $response, array $args): Response
    {
        $user = AuthContext::user();
        if (!$user) {
            return $this->json($response, ['message' => 'Unauthorized'], 401);
        }

        $id = $args['id'];
        $limit = (int) ($request->getQueryParams()['limit'] ?? 50);

        try {
            $timeline = $this->repository->getApplicationTimeline($id, $limit);
            
            return $this->json($response, [
                'data' => $timeline->map(fn($item) => $item->toFrontendArray())
            ]);

        } catch (\Exception $e) {
            return $this->json($response, [
                'message' => 'Zaman çizelgesi alınırken hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * İstatistikler
     */
    public function getStatistics(Request $request, Response $response): Response
    {
        $user = AuthContext::user();
        if (!$user) {
            return $this->json($response, ['message' => 'Unauthorized'], 401);
        }

        try {
            $statistics = $this->repository->getStatistics();
            
            return $this->json($response, [
                'data' => $statistics
            ]);

        } catch (\Exception $e) {
            return $this->json($response, [
                'message' => 'İstatistikler alınırken hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    // Yardımcı metotlar

    private function validateApplicationData(array $data, bool $isUpdate = false): array
    {
        $errors = [];

        if (!$isUpdate && empty($data['applicant_info'])) {
            $errors['applicant_info'] = 'Başvuran bilgileri zorunludur';
        }

        if (!$isUpdate && empty($data['respondent_info'])) {
            $errors['respondent_info'] = 'Cevaplayan bilgileri zorunludur';
        }

        if (!isset($data['application_type'])) {
            $errors['application_type'] = 'Başvuru tipi zorunludur';
        }

        if (!isset($data['subject_matter']) || trim($data['subject_matter']) === '') {
            $errors['subject_matter'] = 'Uyuşmazlık konusu zorunludur';
        }

        if (isset($data['monetary_value']) && $data['monetary_value'] < 0) {
            $errors['monetary_value'] = 'Uyuşmazlık değeri negatif olamaz';
        }

        if (isset($data['application_date']) && !strtotime($data['application_date'])) {
            $errors['application_date'] = 'Geçersiz başvuru tarihi';
        }

        return $errors;
    }

    private function canViewApplication($application, $user): bool
    {
        // Admin tüm başvuları görebilir
        if ($user->hasRole('admin')) {
            return true;
        }

        // Arabulucu sadece kendisine atanan başvuları görebilir
        if ($user->hasRole('mediator') && $application->mediator_id === $user->id) {
            return true;
        }

        // Başvuruyu oluşturan kişi görebilir
        if ($application->created_by === $user->id) {
            return true;
        }

        return false;
    }

    private function canUpdateApplication($application, $user): bool
    {
        // Admin her şeyi güncelleyebilir
        if ($user->hasRole('admin')) {
            return true;
        }

        // Başvuruyu oluşturan kişi güncelleyebilir (sadece belirli durumlar için)
        if ($application->created_by === $user->id && in_array($application->status, ['pending'])) {
            return true;
        }

        // Arabulucu sadece not ekleyebilir
        if ($user->hasRole('mediator') && $application->mediator_id === $user->id) {
            return true;
        }

        return false;
    }

    private function canDeleteApplication($application, $user): bool
    {
        // Admin her şeyi silebilir
        if ($user->hasRole('admin')) {
            return true;
        }

        // Sadece oluşturan kişi silebilir (sadece bekleyen başvular için)
        if ($application->created_by === $user->id && $application->status === 'pending') {
            return true;
        }

        return false;
    }

    private function formatApplication($application): array
    {
        return [
            'id' => $application->id,
            'application_no' => $application->application_no,
            'applicant_name' => $application->getApplicantName(),
            'respondent_name' => $application->getRespondentName(),
            'application_type' => $application->application_type,
            'application_type_label' => $application->getApplicationTypeLabel(),
            'subject_matter' => $application->subject_matter,
            'monetary_value' => $application->monetary_value,
            'formatted_monetary_value' => $application->getFormattedMonetaryValue(),
            'currency' => $application->currency,
            'application_date' => $application->application_date->format('Y-m-d'),
            'formatted_application_date' => $application->getFormattedApplicationDate(),
            'status' => $application->status,
            'status_label' => $application->getStatusLabel(),
            'created_by' => $application->createdBy ? [
                'id' => $application->createdBy->id,
                'name' => $application->createdBy->name,
            ] : null,
            'mediator' => $application->mediator ? [
                'id' => $application->mediator->id,
                'name' => $application->mediator->name,
            ] : null,
            'notes' => $application->notes,
            'created_at' => $application->created_at->toISOString(),
            'updated_at' => $application->updated_at->toISOString(),
        ];
    }

    private function formatApplicationDetail($application): array
    {
        $data = $this->formatApplication($application);
        
        $data['applicant_info'] = $application->applicant_info;
        $data['respondent_info'] = $application->respondent_info;
        $data['metadata'] = $application->metadata;
        $data['documents'] = $application->documents->map(fn($doc) => $this->formatDocument($doc));
        $data['timeline'] = $application->timeline->map(fn($item) => $item->toFrontendArray());

        return $data;
    }

    private function formatDocument($document): array
    {
        return [
            'id' => $document->id,
            'title' => $document->title,
            'document_type' => $document->document_type,
            'document_type_label' => $document->getDocumentTypeLabel(),
            'file_size' => $document->file_size,
            'formatted_file_size' => $document->getFormattedFileSize(),
            'mime_type' => $document->mime_type,
            'is_public' => $document->is_public,
            'file_icon' => $document->getFileIcon(),
            'download_url' => $document->getDownloadUrl(),
            'preview_url' => $document->getPreviewUrl(),
            'uploaded_by' => $document->uploadedBy ? [
                'id' => $document->uploadedBy->id,
                'name' => $document->uploadedBy->name,
            ] : null,
            'created_at' => $document->created_at->toISOString(),
        ];
    }
}
