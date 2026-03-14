<?php

declare(strict_types=1);

use App\Core\Autoloader;
use App\Core\Http\Request;
use App\Core\Http\Session;
use App\Constants\Routes;
use App\Controllers\AuthController;
use App\Controllers\HomeController;

require_once __DIR__ . '/../src/Core/Autoloader.php';

Autoloader::register();

Session::init();

$routes = [
    Routes::HOME    => [HomeController::class, 'index'],
    Routes::SIGNUP  => [AuthController::class, 'signup'],
    Routes::SIGNIN  => [AuthController::class, 'signin'],
    Routes::SIGNOUT => [AuthController::class, 'signout'],
    Routes::DELETE  => [AuthController::class, 'delete'],
    Routes::MYPAGE  => [AuthController::class, 'mypage'],
];

$url = Request::path();

if (!isset($routes[$url])) {
    http_response_code(404);
    echo '404 Not Found';
    exit;
}

[$controllerClass, $method] = $routes[$url];

$controller = new $controllerClass();
$controller->$method();
