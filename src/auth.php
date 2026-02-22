<?php

declare(strict_types=1);

require_once __DIR__ . '/auth_input.php';

$token  = Csrf::token(); // CSRFトークン生成
$action = Request::query('action', 'signin', ['signin', 'signup']);
$error  = Session::getFlash('error');

switch ($action) {
    case 'signup':
        $title      = 'サインアップ';
        $actionUrl  = '/signup';
        $toggleUrl  = '/auth?action=signin';
        $toggleText = 'サインイン';
        $viewpath   = __DIR__ . '/views/auth/signup.php';

        break;
    case 'signin':
        $title = 'サインイン';
        $actionUrl = '/signin';
        $toggleUrl = '/auth?action=signup';
        $toggleText = 'サインアップ';
        $viewpath = __DIR__ . '/views/auth/signin.php';

        break;
}

ob_start();
require $viewpath;
$content = ob_get_clean();
require __DIR__ . '/views/layouts/default.php';
