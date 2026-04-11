<?php

declare(strict_types=1);

use App\Application\App;

require_once __DIR__ . '/../vendor/autoload.php';

$container = require __DIR__ . '/../src/Bootstrap/dependencies.php';

$app = $container->get(App::class);

$app->run();
