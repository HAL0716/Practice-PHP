<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/core/Html.php';
require_once __DIR__ . '/../src/core/Request.php';
require_once __DIR__ . '/../src/core/Session.php';
require_once __DIR__ . '/../src/core/Csrf.php';

require_once __DIR__ . '/../src/controllers/HomeController.php';

$url = Request::path();

switch ($url) {
    case '/':
        phpinfo();
        exit;

    case '/home':
        $controller = new HomeController();
        $controller->index();
        exit;

    case '/auth':
        require __DIR__ . '/../src/auth.php';
        exit;

    case '/signup':
        require __DIR__ . '/../src/register.php';
        exit;

    case '/signin':
        require __DIR__ . '/../src/login.php';
        exit;

    case '/logout':
        require __DIR__ . '/../src/logout.php';
        exit;

    default:
        http_response_code(404);
        echo '404 Not Found';
        exit;
}
