<?php

namespace App\Controllers;

use App\Models\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class UserController extends Controller
{
    public function index(Request $request, Response $response): Response
    {
        $users = User::with('roles')->get()->map(function (User $user) {
            return [
                'id' => $user->id,
                'fullName' => $user->name,
                'email' => $user->email,
                'status' => $user->deleted_at ? 'inactive' : 'active',
                'roles' => $user->roles->pluck('name')->all(),
                'createdAt' => $user->created_at ? $user->created_at->toDateTimeString() : null,
                'updatedAt' => $user->updated_at ? $user->updated_at->toDateTimeString() : null,
            ];
        });

        return $this->json($response, $users->all());
    }
}

