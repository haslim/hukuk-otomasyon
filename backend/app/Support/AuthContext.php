<?php

namespace App\Support;

use App\Models\User;

class AuthContext
{
    private static ?User $user = null;

    public static function setUser(?User $user): void
    {
        self::$user = $user;
    }

    public static function user(): ?User
    {
        return self::$user;
    }
}
