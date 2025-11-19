<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationDocument extends Model
{
    use SoftDeletes;

    protected $table = 'application_documents';

    protected $fillable = [
        'application_id',
        'document_type',
        'title',
        'file_path',
        'file_size',
        'mime_type',
        'uploaded_by',
        'ocr_text',
        'ai_summary',
        'is_public',
        'metadata',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'is_public' => 'boolean',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // İlişkiler
    public function application(): BelongsTo
    {
        return $this->belongsTo(ArbitrationApplication::class, 'application_id');
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // Yardımcı metotlar
    public function getDocumentTypeLabel(): string
    {
        $labels = [
            'basvuru_dilekcesi' => 'Başvuru Dilekçesi',
            'delil' => 'Delil',
            'vekaletname' => 'Vekaletname',
            'kimlik' => 'Kimlik',
            'sirket_belgesi' => 'Şirket Belgesi',
            'vergi_borcu_yoktur' => 'Vergi Borcu Yoktur Yazısı',
            'adres_kaydi' => 'Adres Kaydı',
            'diger' => 'Diğer',
        ];

        return $labels[$this->document_type] ?? $this->document_type;
    }

    public function getFormattedFileSize(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getFileIcon(): string
    {
        $extension = strtolower(pathinfo($this->title, PATHINFO_EXTENSION));
        
        $iconMap = [
            'pdf' => '📄',
            'doc' => '📝',
            'docx' => '📝',
            'xls' => '📊',
            'xlsx' => '📊',
            'jpg' => '🖼️',
            'jpeg' => '🖼️',
            'png' => '🖼️',
            'gif' => '🖼️',
            'txt' => '📄',
            'rtf' => '📄',
        ];

        return $iconMap[$extension] ?? '📄';
    }

    public function isImage(): bool
    {
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
        $extension = strtolower(pathinfo($this->title, PATHINFO_EXTENSION));
        return in_array($extension, $imageExtensions);
    }

    public function isPdf(): bool
    {
        return strtolower(pathinfo($this->title, PATHINFO_EXTENSION)) === 'pdf';
    }

    public function getDownloadUrl(): string
    {
        return '/api/arbitration/documents/' . $this->id . '/download';
    }

    public function getPreviewUrl(): ?string
    {
        if ($this->isImage()) {
            return '/api/arbitration/documents/' . $this->id . '/preview';
        }
        
        if ($this->isPdf()) {
            return '/api/arbitration/documents/' . $this->id . '/preview';
        }

        return null;
    }

    // Dosya işlemleri
    public function getFilePath(): string
    {
        return storage_path('app/public/' . $this->file_path);
    }

    public function fileExists(): bool
    {
        return file_exists($this->getFilePath());
    }

    public function deleteFile(): bool
    {
        if ($this->fileExists()) {
            return unlink($this->getFilePath());
        }
        return true;
    }

    // Scope'lar
    public function scopeByType($query, $type)
    {
        return $query->where('document_type', $type);
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopePrivate($query)
    {
        return $query->where('is_public', false);
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    // Model events
    protected static function boot()
    {
        parent::boot();

        // Silinirken dosyayı da sil
        static::deleting(function ($document) {
            $document->deleteFile();
        });
    }

    // OCR ve AI özetleme için hazır metotlar
    public function processWithOcr(): ?string
    {
        if (!$this->isPdf() && !$this->isTextFile()) {
            return null;
        }

        // TODO: OCR servisi entegrasyonu
        // Şimdilik placeholder
        return "OCR işlemi henüz entegre edilmemiştir.";
    }

    public function generateAiSummary(): ?string
    {
        if (empty($this->ocr_text)) {
            return null;
        }

        // TODO: AI servis entegrasyonu (GPT/OpenAI)
        // Şimdilik placeholder
        return "AI özetleme henüz entegre edilmemiştir.";
    }

    private function isTextFile(): bool
    {
        $textExtensions = ['txt', 'rtf', 'doc', 'docx'];
        $extension = strtolower(pathinfo($this->title, PATHINFO_EXTENSION));
        return in_array($extension, $textExtensions);
    }
}
