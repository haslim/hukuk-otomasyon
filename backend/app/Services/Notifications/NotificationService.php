<?php

namespace App\Services\Notifications;

use App\Repositories\NotificationRepository;
use Carbon\Carbon;

class NotificationService
{
    public function __construct(private readonly NotificationRepository $notifications)
    {
    }

    public function pending()
    {
        return $this->notifications->all(['status' => 'pending']);
    }

    public function scheduleCaseReminder(string $caseId, string $subject, Carbon $remindAt)
    {
        return $this->notifications->enqueue([
            'case_id' => $caseId,
            'subject' => $subject,
            'payload' => ['remind_at' => $remindAt->toIso8601String()],
            'status' => 'pending'
        ]);
    }

    public function dispatch(array $notification)
    {
        // Integration hook for e-mail / SMS providers
    }
}
