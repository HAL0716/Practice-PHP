<?php

declare(strict_types=1);

use App\Core\Http\Request;
use App\Core\Http\Session;
use App\Constants\Routes;
use App\Controllers\UserController;
use App\Controllers\PostController;

require_once __DIR__ . '/../vendor/autoload.php';

Session::init();

$routes = [
    Routes::USER_SIGNUP  => [UserController::class, 'signup'],
    Routes::USER_SIGNIN  => [UserController::class, 'signin'],
    Routes::USER_SIGNOUT => [UserController::class, 'signout'],
    Routes::USER_DELETE  => [UserController::class, 'delete'],
    Routes::USER_MYPAGE  => [UserController::class, 'mypage'],

    Routes::POST_HOME   => [PostController::class, 'home'],
    Routes::POST_DELETE => [PostController::class, 'delete'],
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
