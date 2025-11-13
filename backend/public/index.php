<?php

$app = require __DIR__ . '/../bootstrap/app.php';

(require __DIR__ . '/../routes/api.php')($app);

$app->run();
