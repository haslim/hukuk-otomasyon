<?php

require __DIR__ . '/../vendor/autoload.php';

$files = glob(__DIR__ . '/migrations/*.php');
sort($files);

foreach ($files as $file) {
    $migration = require $file;
    if (is_object($migration) && method_exists($migration, 'up')) {
        $migration->up();
        echo basename($file) . " migrated" . PHP_EOL;
    }
}
