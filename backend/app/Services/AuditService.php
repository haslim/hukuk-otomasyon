<?php

namespace App\Services;

use App\Repositories\AuditRepository;

class AuditService
{
    public function __construct(private readonly AuditRepository $audit)
    {
    }

    public function log(string $entityType, string $entityId, string $action, array $metadata = []): void
    {
        $this->audit->create([
            'user_id' => auth()?->id,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'action' => $action,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            'metadata' => $metadata
        ]);
    }
}
