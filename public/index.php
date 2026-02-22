<?php

declare(strict_types=1);

require_once __DIR__ . '../src/constants/FormFields.php';

require_once __DIR__ . '/../src/core/Html.php';
require_once __DIR__ . '/../src/core/Request.php';
require_once __DIR__ . '/../src/core/Session.php';
require_once __DIR__ . '/../src/core/Csrf.php';

require_once __DIR__ . '/../src/controllers/HomeController.php';
require_once __DIR__ . '/../src/controllers/AuthController.php';

$url = Request::path();

switch ($url) {
    case '/':
        phpinfo();
        exit;

    case '/home':
        $controller = new HomeController();
        $controller->index();
        exit;

    case '/signup':
        $controller = new AuthController();
        $controller->signup();
        exit;

    case '/signin':
        $controller = new AuthController();
        $controller->signin();
        exit;

    case '/signout':
        $controller = new AuthController();
        $controller->signout();
        exit;

    default:
        http_response_code(404);
        echo '404 Not Found';
        exit;
}
