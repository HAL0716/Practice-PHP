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

$url = Request::path();

switch ($url) {

    case Routes::HOME:
        $controller = new HomeController();
        $controller->index();
        exit;

    case Routes::SIGNUP:
        $controller = new AuthController();
        $controller->signup();
        exit;

    case Routes::SIGNIN:
        $controller = new AuthController();
        $controller->signin();
        exit;

    case Routes::SIGNOUT:
        $controller = new AuthController();
        $controller->signout();
        exit;

    case Routes::DELETE:
        $controller = new AuthController();
        $controller->delete();
        exit;

    case Routes::MYPAGE:
        $controller = new AuthController();
        $controller->mypage();
        exit;

    default:
        http_response_code(404);
        echo '404 Not Found';
        exit;
}
