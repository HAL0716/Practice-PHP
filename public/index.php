<?php

declare(strict_types=1);

require __DIR__ . '/../src/helpers.php';

$url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$url = rtrim($url, '/');

switch ($url) {
    case '':
        phpinfo();
        exit;

    case '/home':
        require __DIR__ . '/../src/home.php';
        exit;

    case '/auth':
        require __DIR__ . '/../src/auth.php';
        exit;

    case '/register':
        require __DIR__ . '/../src/register.php';
        exit;

    case '/login':
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
