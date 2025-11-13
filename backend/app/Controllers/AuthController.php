<?php

namespace App\Controllers;

use App\Services\AuthService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AuthController extends Controller
{
    private AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    public function login(Request $request, Response $response): Response
    {
        $credentials = (array) $request->getParsedBody();
        $result = $this->authService->attempt($credentials['email'] ?? '', $credentials['password'] ?? '');

        if (!$result) {
            return $this->json($response, ['message' => 'Invalid credentials'], 401);
        }

        return $this->json($response, $result);
    }

    public function logout(Request $request, Response $response): Response
    {
        return $this->json($response, ['message' => 'Logged out']);
    }
}
