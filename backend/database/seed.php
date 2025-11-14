<?php

require_once __DIR__ . '/../bootstrap/app.php';

use Database\Seeders\AdminUserSeeder;

$seeders = [
    AdminUserSeeder::class,
];

foreach ($seeders as $class) {
    if (!class_exists($class)) {
        echo "Seeder bulunamadı: {$class}" . PHP_EOL;
        continue;
    }

    $seeder = new $class();
    $seeder->run();
}

