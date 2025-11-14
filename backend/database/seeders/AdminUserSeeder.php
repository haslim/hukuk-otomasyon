<?php

namespace Database\Seeders;

use App\Models\User;

class AdminUserSeeder
{
    private const NAME = 'Ali Haydar Aslim';
    private const EMAIL = 'alihaydraslim@gmail.com';
    private const PASSWORD = 'test123456';

    public function run(): void
    {
        $hashedPassword = password_hash(self::PASSWORD, PASSWORD_BCRYPT);

        $user = User::withTrashed()->firstOrNew(['email' => self::EMAIL]);

        $user->name = self::NAME;
        $user->password = $hashedPassword;
        if (!isset($user->phone)) {
            $user->phone = null;
        }

        // ensure not soft-deleted
        if (isset($user->deleted_at)) {
            $user->deleted_at = null;
        }

        $user->save();

        echo 'Admin user seeded: ' . self::EMAIL . PHP_EOL;
    }
}

