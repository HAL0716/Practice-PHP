<?php

declare(strict_types=1);

use App\Core\Autoloader;
use App\Core\Http\Request;
use App\Core\Http\Session;
use App\Constants\Routes;
use App\Controllers\UserController;
use App\Controllers\PostController;

require_once __DIR__ . '/../src/Core/Autoloader.php';

Autoloader::register();

Session::init();

$routes = [
    Routes::HOME    => [PostController::class, 'home'],
    Routes::DELETE_POST => [PostController::class, 'delete'],
    Routes::SIGNUP  => [UserController::class, 'signup'],
    Routes::SIGNIN  => [UserController::class, 'signin'],
    Routes::SIGNOUT => [UserController::class, 'signout'],
    Routes::DELETE  => [UserController::class, 'delete'],
    Routes::MYPAGE  => [UserController::class, 'mypage'],
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
