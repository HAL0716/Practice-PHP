<?php

declare(strict_types=1);

require_once __DIR__ . '/auth_input.php';

$token = Csrf::token(); // CSRFトークン生成

// アクション取得
$action = Request::query('action', 'login', ['login', 'register']);

$labels = [
    'login'    => 'ログイン',
    'register' => '新規作成',
];

$title = $labels[$action] ?? 'ログイン';
$isRegister = $action === 'register';
$toggleUrl  = $isRegister ? '/auth?action=login' : '/auth?action=register';
$toggleText = $isRegister ? 'ログイン' : '新規作成';

$error = Session::getFlash('error');

ob_start();
require __DIR__ . "/views/auth.php";
$content = ob_get_clean();
require __DIR__ . '/views/layouts/default.php';
