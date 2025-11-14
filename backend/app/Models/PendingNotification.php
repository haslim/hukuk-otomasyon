<?php

namespace App\Models;

class PendingNotification extends BaseModel
{
    protected $table = 'pending_notifications';

    protected $casts = [
        'payload' => 'array'
    ];
}
