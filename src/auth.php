<?php

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/auth_input.php';

CsrfToken::generate(); // CSRFトークン生成

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
?>

<h2><?= Html::escape($title) ?></h2>

<?php if ($error !== null): ?>
    <p style="color:red;"><?= Html::escape($error) ?></p>
<?php endif; ?>

<form action="/<?= Html::escape($action) ?>" method="post">
    <input type="hidden" name="action" value="<?= Html::escape($action) ?>">
    <input type="hidden" name="token" value="<?= Html::escape(Session::get('token')) ?>">

    <?php if ($isRegister): ?>
        <input type="text" name="<?= Html::escape(AuthInput::KEY_NAME) ?>" placeholder="username" required><br>
    <?php endif; ?>

    <input type="email"    name="<?= Html::escape(AuthInput::KEY_MAIL) ?>" placeholder="email"    required><br>
    <input type="password" name="<?= Html::escape(AuthInput::KEY_PASS) ?>" placeholder="password" required><br>

    <button><?= Html::escape($title) ?></button>
</form>

<p>
    <a href="<?= Html::escape($toggleUrl) ?>"><?= Html::escape($toggleText) ?></a>
</p>

<?php
$content = ob_get_clean();
require __DIR__ . '/layouts/default.php';
