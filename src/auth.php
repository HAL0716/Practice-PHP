<?php

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/auth_input.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    setToken(); // CSRFトークン生成
}

// アクション取得
$action = getStr($_GET, 'action', 'login', ['login', 'register']);

$labels = [
    'login'    => 'ログイン',
    'register' => '新規作成',
];

$title = $labels[$action] ?? 'ログイン';
$isRegister = $action === 'register';
$toggleUrl  = $isRegister ? '/auth?action=login' : '/auth?action=register';
$toggleText = $isRegister ? 'ログイン' : '新規作成';

$error = getError();

ob_start();
?>

<h2><?= e($title) ?></h2>

<?php if ($error !== null): ?>
    <p style="color:red;"><?= e($error) ?></p>
<?php endif; ?>

<form action="/<?= e($action) ?>" method="post">
    <input type="hidden" name="action" value="<?= e($action) ?>">
    <input type="hidden" name="token" value="<?= e($_SESSION['token'] ?? '') ?>">

    <?php if ($isRegister): ?>
        <input type="text" name="<?= e(AuthInput::KEY_NAME) ?>" placeholder="username" required><br>
    <?php endif; ?>

    <input type="email"    name="<?= e(AuthInput::KEY_MAIL) ?>" placeholder="email"    required><br>
    <input type="password" name="<?= e(AuthInput::KEY_PASS) ?>" placeholder="password" required><br>

    <button><?= e($title) ?></button>
</form>

<p>
    <a href="<?= e($toggleUrl) ?>"><?= e($toggleText) ?></a>
</p>

<?php
$content = ob_get_clean();
require __DIR__ . '/layouts/default.php';
