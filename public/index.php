<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/Core/Autoloader.php';
\App\Core\Autoloader::register();

\App\Core\Http\Session::init();

$url = \App\Core\Http\Request::path();

switch ($url) {

    case \App\Constants\Routes::HOME:
        $controller = new \App\Controllers\HomeController();
        $controller->index();
        exit;

    case \App\Constants\Routes::SIGNUP:
        $controller = new \App\Controllers\AuthController();
        $controller->signup();
        exit;

    case \App\Constants\Routes::SIGNIN:
        $controller = new \App\Controllers\AuthController();
        $controller->signin();
        exit;

    case \App\Constants\Routes::SIGNOUT:
        $controller = new \App\Controllers\AuthController();
        $controller->signout();
        exit;

    case \App\Constants\Routes::MYPAGE:
        $controller = new \App\Controllers\AuthController();
        $controller->mypage();
        exit;

    default:
        http_response_code(404);
        echo '404 Not Found';
        exit;
}
