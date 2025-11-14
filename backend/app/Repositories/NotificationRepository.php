<?php

namespace App\Repositories;

use App\Models\Notification;
use App\Models\PendingNotification;

class NotificationRepository extends BaseRepository
{
    public function __construct(Notification $model)
    {
        parent::__construct($model);
    }

    public function enqueue(array $payload): PendingNotification
    {
        return PendingNotification::create($payload);
    }
}
