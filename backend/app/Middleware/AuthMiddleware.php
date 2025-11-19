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
        // Multiple ways to get Authorization header
        $header = $request->getHeaderLine("Authorization");
        if (empty($header) && isset($_SERVER["HTTP_AUTHORIZATION"])) {
            $header = $_SERVER["HTTP_AUTHORIZATION"];
        }
        if (empty($header) && isset($_SERVER["REDIRECT_HTTP_AUTHORIZATION"])) {
            $header = $_SERVER["REDIRECT_HTTP_AUTHORIZATION"];
        }

        if (empty($header)) {
            return $this->unauthorized("Missing Authorization header");
        }

        if (!str_starts_with($header, "Bearer ")) {
            return $this->unauthorized("Invalid token format. Expected: Bearer <token>");
        }

        $token = substr($header, 7);
        
        // Check if JWT_SECRET is set
        if (empty($_ENV["JWT_SECRET"])) {
            error_log("JWT_SECRET is not set in environment");
            return $this->unauthorized("Server configuration error");
        }

        $user = $this->authService->validate($token);
        if (!$user) {
            error_log("Token validation failed for token: " . substr($token, 0, 20) . "...");
            return $this->unauthorized("Invalid or expired token");
        }

        AuthContext::setUser($user);
        
        // Also set user as request attribute for compatibility
        $request = $request->withAttribute('user', $user);
        
        return $handler->handle($request);
    }

    private function unauthorized(string $message = "Unauthorized"): Response
    {
        error_log("Authentication failed: " . $message);
        $responseFactory = AppFactory::determineResponseFactory();
        $response = $responseFactory->createResponse(401);
        $response->getBody()->write(json_encode([
            "message" => $message,
            "timestamp" => time(),
            "path" => $_SERVER["REQUEST_URI"] ?? "unknown"
        ]));
        return $response->withHeader("Content-Type", "application/json");
    }
}
