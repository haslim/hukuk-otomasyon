<?php

$app = require __DIR__ . '/../bootstrap/app.php';

// Web / root routes (health check, etc.)
(require __DIR__ . '/../routes/web.php')($app);

// API routes
(require __DIR__ . '/../routes/api.php')($app);

$app->run();
