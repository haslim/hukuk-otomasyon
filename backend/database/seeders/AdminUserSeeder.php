<?php

namespace Database\Seeders;

use App\Models\User;

class AdminUserSeeder
{
    private const NAME = 'Ali Haydar AslÄ±m';
    private const EMAIL = 'alihaydraslim@gmail.com';
    private const PASSWORD = 'test123456';

    public function run(): void
    {
        $hashedPassword = password_hash(self::PASSWORD, PASSWORD_BCRYPT);

        $user = User::withTrashed()->where('email', self::EMAIL)->first();

        if (!$user) {
            User::create([
                'name' => self::NAME,
                'email' => self::EMAIL,
                'password' => $hashedPassword,
                'phone' => null,
            ]);

            echo "Admin kullanÄ±cÄ±sÄ± oluÅŸturuldu: " . self::EMAIL . PHP_EOL;
            return;
        }

        if ($user->trashed()) {
            $user->restore();
        }

        $user->name = self::NAME;
        $user->password = $hashedPassword;
        $user->save();

        echo "Admin kullanÄ±cÄ±sÄ± gÃ¼ncellendi: " . self::EMAIL . PHP_EOL;
    }
}
