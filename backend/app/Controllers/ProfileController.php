<?php

namespace App\Controllers;

use App\Support\AuthContext;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ProfileController extends Controller
{
    public function me(Request $request, Response $response): Response
    {
        $user = AuthContext::user();
        if (!$user) {
            return $this->json($response, ['message' => 'Unauthorized'], 401);
        }

        return $this->json($response, [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'title' => $user->title ?? null,
            'avatarUrl' => $user->avatar_url ?? null,
        ]);
    }

    public function update(Request $request, Response $response): Response
    {
        $user = AuthContext::user();
        if (!$user) {
            return $this->json($response, ['message' => 'Unauthorized'], 401);
        }

        $data = (array) $request->getParsedBody();

        if (isset($data['name']) && is_string($data['name'])) {
            $user->name = $data['name'];
        }

        if (isset($data['title']) && is_string($data['title'])) {
            $user->title = $data['title'];
        }

        if (isset($data['avatarUrl']) && is_string($data['avatarUrl'])) {
            $user->avatar_url = $data['avatarUrl'];
        }

        // E-mail değişimine izin vermek istemezsek bu satırı kaldırabiliriz.
        if (isset($data['email']) && is_string($data['email'])) {
            $user->email = $data['email'];
        }

        $user->save();

        return $this->json($response, [
            'message' => 'Profil güncellendi',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'title' => $user->title ?? null,
                'avatarUrl' => $user->avatar_url ?? null,
            ],
        ]);
    }
}

