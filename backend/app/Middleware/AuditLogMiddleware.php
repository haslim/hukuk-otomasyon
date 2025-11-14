<?php

namespace App\Middleware;

use App\Models\AuditLog;
use App\Repositories\AuditRepository;
use App\Services\AuditService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Ramsey\Uuid\Uuid;

class AuditLogMiddleware implements MiddlewareInterface
{
    private AuditService $auditService;

    public function __construct(private readonly string $entityType, private readonly ?string $action = null)
    {
        $this->auditService = new AuditService(new AuditRepository(new AuditLog()));
    }

    public function process(Request $request, RequestHandlerInterface $handler): Response
    {
        $response = $handler->handle($request);

        $route = $request->getAttribute('route');
        $entityId = $route?->getArgument('id') ?? Uuid::uuid4()->toString();

        $this->auditService->log(
            $this->entityType,
            $entityId,
            $this->action ?? $request->getMethod(),
            [
                'path' => (string) $request->getUri(),
                'status' => $response->getStatusCode()
            ]
        );

        return $response;
    }
}
