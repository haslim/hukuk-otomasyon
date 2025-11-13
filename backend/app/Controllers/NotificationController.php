<?php

namespace App\Controllers;

use App\Models\Notification;
use App\Repositories\NotificationRepository;
use App\Services\Notifications\NotificationService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class NotificationController extends Controller
{
    private NotificationService $notifications;

    public function __construct()
    {
        $this->notifications = new NotificationService(new NotificationRepository(new Notification()));
    }

    public function index(Request $request, Response $response): Response
    {
        $pending = $this->notifications->pending();
        return $this->json($response, $pending->toArray());
    }

    public function dispatch(Request $request, Response $response): Response
    {
        $payload = (array) $request->getParsedBody();
        $this->notifications->dispatch($payload);
        return $this->json($response, ['message' => 'Notification triggered']);
    }
}
