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

        // Token expiration (default: 2 hours = 7200 seconds)
        $ttl = (int) ($_ENV['JWT_EXPIRE'] ?? 7200);
        if ($ttl <= 0) {
            $ttl = 7200;
        }

        $tokenId = Uuid::uuid4()->toString();
        $payload = [
            'iss' => 'bgaofis',
            'sub' => $user->id,
            'jti' => $tokenId,
            'exp' => time() + $ttl,
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
            $user = User::find($decoded->sub);
            if ($user && isset($decoded->permissions)) {
                $user->setAttribute('token_permissions', (array) $decoded->permissions);
            }
            return $user;
        } catch (\Throwable $th) {
            return null;
        }
    }
}
