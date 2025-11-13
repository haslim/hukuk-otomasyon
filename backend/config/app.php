<?php

return [
    'name' => 'BGAofis Hukuk Otomasyon',
    'env' => $_ENV['APP_ENV'] ?? 'local',
    'debug' => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOL),
    'timezone' => 'Europe/Istanbul'
];
