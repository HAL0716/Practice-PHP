<?php

declare(strict_types=1);

use App\Application\App;
use App\Infrastructure\Http\Request;

require_once __DIR__ . '/../vendor/autoload.php';

$container = require __DIR__ . '/../src/Bootstrap/dependencies.php';

$request = $container->get(Request::class);

$routes = $container->get('routes');

$app = $container->get(App::class);

$app->run();
