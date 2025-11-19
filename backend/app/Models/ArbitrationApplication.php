<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArbitrationApplication extends Model
{
    use SoftDeletes;

    protected $table = 'arbitration_applications';

    protected $fillable = [
        'application_no',
        'applicant_info',
        'respondent_info',
        'application_type',
        'subject_matter',
        'monetary_value',
        'currency',
        'application_date',
        'status',
        'created_by',
        'mediator_id',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'applicant_info' => 'array',
        'respondent_info' => 'array',
        'monetary_value' => 'decimal:2',
        'application_date' => 'date',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // İlişkiler
    public function documents(): HasMany
    {
        return $this->hasMany(ApplicationDocument::class, 'application_id');
    }

    public function timeline(): HasMany
    {
        return $this->hasMany(ApplicationTimeline::class, 'application_id')
            ->orderBy('created_at', 'desc');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function mediator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mediator_id');
    }

    // Yardımcı metotlar
    public function getStatusLabel(): string
    {
        $labels = [
            'pending' => 'Beklemede',
            'accepted' => 'Kabul Edildi',
            'rejected' => 'Reddedildi',
            'in_progress' => 'İşlemde',
            'completed' => 'Tamamlandı',
            'cancelled' => 'İptal Edildi',
        ];

        return $labels[$this->status] ?? $this->status;
    }

    public function getApplicationTypeLabel(): string
    {
        $labels = [
            'ihtiyati' => 'İhtiyati',
            'ihtiyati_tedbir' => 'İhtiyati Tedbir',
            'ticari' => 'Ticari',
            'is_hukuku' => 'İş Hukuku',
            'tuketici' => 'Tüketici',
            'icra' => 'İcra',
            'diger' => 'Diğer',
        ];

        return $labels[$this->application_type] ?? $this->application_type;
    }

    public function getApplicantName(): string
    {
        if (!isset($this->applicant_info['name'])) {
            return 'Bilgi Yok';
        }

        $name = $this->applicant_info['name'];
        
        if (isset($this->applicant_info['company_name'])) {
            $name = $this->applicant_info['company_name'];
        }

        if (isset($this->applicant_info['type']) && $this->applicant_info['type'] === 'legal') {
            $name .= ' (Şirket)';
        } else {
            $name .= ' (Şahıs)';
        }

        return $name;
    }

    public function getRespondentName(): string
    {
        if (!isset($this->respondent_info['name'])) {
            return 'Bilgi Yok';
        }

        $name = $this->respondent_info['name'];
        
        if (isset($this->respondent_info['company_name'])) {
            $name = $this->respondent_info['company_name'];
        }

        if (isset($this->respondent_info['type']) && $this->respondent_info['type'] === 'legal') {
            $name .= ' (Şirket)';
        } else {
            $name .= ' (Şahıs)';
        }

        return $name;
    }

    public function getFormattedMonetaryValue(): string
    {
        if (!$this->monetary_value) {
            return 'Belirtilmemiş';
        }

        $formatter = new \NumberFormatter('tr_TR', \NumberFormatter::CURRENCY);
        return $formatter->formatCurrency($this->monetary_value, $this->currency);
    }

    public function getFormattedApplicationDate(): string
    {
        return $this->application_date->format('d.m.Y');
    }

    // Scope'lar
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByApplicationType($query, $type)
    {
        return $query->where('application_type', $type);
    }

    public function scopeByMediator($query, $mediatorId)
    {
        return $query->where('mediator_id', $mediatorId);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['pending', 'accepted', 'in_progress']);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    // Başvuru numarası oluşturma
    public static function generateApplicationNo(): string
    {
        $year = date('Y');
        $prefix = 'ARB-' . $year . '-';
        
        $lastApplication = self::where('application_no', 'like', $prefix . '%')
            ->orderBy('application_no', 'desc')
            ->first();

        if ($lastApplication) {
            $lastNumber = (int) str_replace($prefix, '', $lastApplication->application_no);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
    }

    // Zaman çizelgesine olay ekleme
    public function addTimelineEvent(string $eventType, string $description, ?array $eventData = null, ?User $user = null): void
    {
        $this->timeline()->create([
            'event_type' => $eventType,
            'description' => $description,
            'event_data' => $eventData,
            'user_id' => $user?->id,
        ]);
    }

    // Durum değiştirme
    public function changeStatus(string $newStatus, ?User $user = null, ?string $note = null): void
    {
        $oldStatus = $this->status;
        
        $this->update([
            'status' => $newStatus,
        ]);

        $this->addTimelineEvent('status_changed', 
            "Durum {$oldStatus} → {$newStatus} olarak değiştirildi" . ($note ? " ({$note})" : ""),
            [
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'note' => $note,
            ],
            $user
        );
    }
}
