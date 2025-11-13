<?php

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Factory\AppFactory;

class RoleMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly string $permission)
    {
    }

    public function process(Request $request, RequestHandlerInterface $handler): Response
    {
        $user = auth();
        if (!$user || !$user->hasPermission($this->permission)) {
            $responseFactory = AppFactory::determineResponseFactory();
            $response = $responseFactory->createResponse(403);
            $response->getBody()->write(json_encode(['message' => 'Forbidden']));
            return $response->withHeader('Content-Type', 'application/json');
        }

        return $handler->handle($request);
    }
}
