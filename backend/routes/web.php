<?php

use Slim\App;

return function (App $app) {
    $app->get('/', function ($request, $response) {
        $response->getBody()->write(json_encode([
            'status' => 'ok',
            'service' => 'BGAofis API',
            'version' => $_ENV['API_VERSION'] ?? 'v1',
            'timestamp' => time(),
        ]));

        return $response->withHeader('Content-Type', 'application/json');
    });
};

