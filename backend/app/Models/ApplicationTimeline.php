<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationTimeline extends Model
{
    use SoftDeletes;

    protected $table = 'application_timeline';

    protected $fillable = [
        'application_id',
        'event_type',
        'description',
        'event_data',
        'user_id',
    ];

    protected $casts = [
        'event_data' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // İlişkiler
    public function application(): BelongsTo
    {
        return $this->belongsTo(ArbitrationApplication::class, 'application_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Yardımcı metotlar
    public function getEventIcon(): string
    {
        $iconMap = [
            'created' => '📝',
            'updated' => '✏️',
            'document_added' => '📎',
            'document_removed' => '🗑️',
            'status_changed' => '🔄',
            'mediator_assigned' => '👤',
            'mediator_changed' => '👥',
            'note_added' => '📋',
            'session_created' => '📅',
            'session_updated' => '🕐',
            'session_completed' => '✅',
            'payment_added' => '💰',
            'agreement_created' => '🤝',
            'agreement_signed' => '✍️',
            'deadline_added' => '⏰',
            'deadline_passed' => '⏱️',
        ];

        return $iconMap[$this->event_type] ?? '📌';
    }

    public function getEventColor(): string
    {
        $colorMap = [
            'created' => 'blue',
            'updated' => 'gray',
            'document_added' => 'green',
            'document_removed' => 'red',
            'status_changed' => 'orange',
            'mediator_assigned' => 'purple',
            'mediator_changed' => 'pink',
            'note_added' => 'indigo',
            'session_created' => 'cyan',
            'session_updated' => 'teal',
            'session_completed' => 'emerald',
            'payment_added' => 'yellow',
            'agreement_created' => 'lime',
            'agreement_signed' => 'green',
            'deadline_added' => 'amber',
            'deadline_passed' => 'red',
        ];

        return $colorMap[$this->event_type] ?? 'gray';
    }

    public function getFormattedTime(): string
    {
        return $this->created_at->format('d.m.Y H:i');
    }

    public function getTimeAgo(): string
    {
        $now = now();
        $diff = $now->diffInMinutes($this->created_at);

        if ($diff < 1) {
            return 'Az önce';
        } elseif ($diff < 60) {
            return $diff . ' dakika önce';
        } elseif ($diff < 1440) {
            return floor($diff / 60) . ' saat önce';
        } elseif ($diff < 10080) {
            return floor($diff / 1440) . ' gün önce';
        } else {
            return $this->created_at->format('d.m.Y');
        }
    }

    public function isSystemEvent(): bool
    {
        $systemEvents = [
            'created',
            'document_added',
            'document_removed',
            'status_changed',
            'mediator_assigned',
            'mediator_changed',
            'session_created',
            'session_updated',
            'session_completed',
            'deadline_added',
            'deadline_passed',
        ];

        return in_array($this->event_type, $systemEvents);
    }

    public function isUserEvent(): bool
    {
        return !$this->isSystemEvent();
    }

    // Scope'lar
    public function scopeByEventType($query, $eventType)
    {
        return $query->where('event_type', $eventType);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeSystemEvents($query)
    {
        $systemEvents = [
            'created',
            'document_added',
            'document_removed',
            'status_changed',
            'mediator_assigned',
            'mediator_changed',
            'session_created',
            'session_updated',
            'session_completed',
            'deadline_added',
            'deadline_passed',
        ];

        return $query->whereIn('event_type', $systemEvents);
    }

    public function scopeUserEvents($query)
    {
        $systemEvents = [
            'created',
            'document_added',
            'document_removed',
            'status_changed',
            'mediator_assigned',
            'mediator_changed',
            'session_created',
            'session_updated',
            'session_completed',
            'deadline_added',
            'deadline_passed',
        ];

        return $query->whereNotIn('event_type', $systemEvents);
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // Önyüze friendly veri dönüşümü
    public function toFrontendArray(): array
    {
        return [
            'id' => $this->id,
            'event_type' => $this->event_type,
            'description' => $this->description,
            'icon' => $this->getEventIcon(),
            'color' => $this->getEventColor(),
            'event_data' => $this->event_data,
            'user' => $this->user ? [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ] : null,
            'formatted_time' => $this->getFormattedTime(),
            'time_ago' => $this->getTimeAgo(),
            'is_system_event' => $this->isSystemEvent(),
            'is_user_event' => $this->isUserEvent(),
            'created_at' => $this->created_at->toISOString(),
        ];
    }
}
