<?php

declare(strict_types=1);

require __DIR__ . '/../src/helpers.php';

$url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$url = rtrim($url, '/');

switch ($url) {
    case '':
        phpinfo();
        exit;

    default:
        http_response_code(404);
        echo '404 Not Found';
        exit;
}
