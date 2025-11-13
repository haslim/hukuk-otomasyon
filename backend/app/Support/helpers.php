<?php

use App\Support\AuthContext;
use App\Models\User;

if (!function_exists('auth')) {
    function auth(): ?User
    {
        return AuthContext::user();
    }
}
