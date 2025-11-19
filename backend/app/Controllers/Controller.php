<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use JsonException;

abstract class Controller
{
    protected function json(Response $response, array $data, int $status = 200): Response
    {
        try {
            $response->getBody()->write(json_encode($data, JSON_THROW_ON_ERROR));
            return $response
                ->withHeader('Content-Type', 'application/json; charset=utf-8')
                ->withStatus($status);
        } catch (JsonException $e) {
            $errorResponse = $response->withStatus(500);
            $errorResponse->getBody()->write(json_encode([
                'error' => 'JSON encoding error',
                'message' => $e->getMessage()
            ]));
            return $errorResponse->withHeader('Content-Type', 'application/json; charset=utf-8');
        }
    }
}
