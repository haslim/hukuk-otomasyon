<?php

namespace App\Middleware;

use App\Services\AuthService;
use App\Support\AuthContext;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Factory\AppFactory;

class AuthMiddleware implements MiddlewareInterface
{
    private AuthService $authService;

    public function __construct(?AuthService $authService = null)
    {
        $this->authService = $authService ?? new AuthService();
    }

    public function process(Request $request, RequestHandlerInterface $handler): Response
    {
        $header = $request->getHeaderLine('Authorization');
        if ($header === '' && isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $header = $_SERVER['HTTP_AUTHORIZATION'];
        }
        if ($header === '' && isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            $header = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
        }

        if (!str_starts_with($header, 'Bearer ')) {
            return $this->unauthorized();
        }

        $token = substr($header, 7);
        $user = $this->authService->validate($token);
        if (!$user) {
            return $this->unauthorized();
        }

        AuthContext::setUser($user);
        return $handler->handle($request);
    }

    private function unauthorized(): Response
    {
        $responseFactory = AppFactory::determineResponseFactory();
        $response = $responseFactory->createResponse(401);
        $response->getBody()->write(json_encode(['message' => 'Unauthorized']));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
