<?php

declare(strict_types=1);

function e(
    string $string,
    int $flags = ENT_QUOTES,
    ?string $encoding = 'UTF-8',
    bool $double_encode = true
): string {
    return htmlspecialchars($string, $flags, $encoding, $double_encode);
}

function setToken(): void
{
    if (empty($_SESSION['token'])) {
        $_SESSION['token'] = bin2hex(random_bytes(32));
    }
}

function checkToken(): void
{
    if (!isset($_SESSION['token'], $_POST['token']) || !hash_equals($_SESSION['token'], $_POST['token'])) {
        $_SESSION['error'] = '不正なリクエストです';
        header('Location: /auth');
        exit;
    }
}
