<?php

declare(strict_types=1);

use App\Infrastructure\Http\Request;

require_once __DIR__ . '/../vendor/autoload.php';

$container = require __DIR__ . '/../src/Bootstrap/container.php';

$request = $container->get(Request::class);
$url = $request->path();

$routes = require __DIR__ . '/../src/Bootstrap/routes.php';

if (!isset($routes[$url])) {
    http_response_code(404);
    echo '404 Not Found';
    exit;
}

[$controllerClass, $method] = $routes[$url];

$controller = $container->get($controllerClass);
$controller->$method();
