<?php

namespace App\Services;

use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Ramsey\Uuid\Uuid;

class AuthService
{
    public function attempt(string $email, string $password): ?array
    {
        $user = User::where('email', $email)->first();
        if (!$user || !password_verify($password, $user->password)) {
            return null;
        }

        $tokenId = Uuid::uuid4()->toString();
        $payload = [
            'iss' => 'bgaofis',
            'sub' => $user->id,
            'jti' => $tokenId,
            'exp' => time() + 60 * 60 * 4,
            'permissions' => $user->roles()->with('permissions')->get()
                ->flatMap(fn ($role) => $role->permissions->pluck('key'))
                ->unique()
                ->values()
        ];

        $token = JWT::encode($payload, $_ENV['JWT_SECRET'], 'HS256');

        return ['token' => $token, 'user' => $user];
    }

    public function validate(string $token): ?User
    {
        try {
            $decoded = JWT::decode($token, new Key($_ENV['JWT_SECRET'], 'HS256'));
            return User::find($decoded->sub);
        } catch (\Throwable $th) {
            return null;
        }
    }
}
