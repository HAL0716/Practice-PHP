<?php

declare(strict_types=1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    setToken();
}

require_once __DIR__ . '/auth_input.php';

$action = getStr($_GET, 'action', 'login', ['login', 'register']);

$labels = [
    'login'    => 'ログイン',
    'register' => 'サインイン',
];

$title = $labels[$action];

$isRegister = $action === 'register';
$isAction   = $isRegister ? 'register' : 'login';
$toggleUrl  = $isRegister ? '/auth?action=login' : '/auth?action=register';
$toggleText = $isRegister ? 'ログイン' : '新規作成';

ob_start();
?>

<h2><?= e($title) ?></h2>

<?php if (isset($_SESSION['error'])): ?>
    <p style="color: red;"><?= e($_SESSION['error']) ?></p>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<form action="/<?= e($isAction) ?>" method="post">
    <input type="hidden" name="action" value="<?= e($action) ?>">
    <input type="hidden" name="token" value="<?= e($_SESSION['token'] ?? '') ?>">

    <?php if ($isRegister): ?>
        <input type="text" name="<?= e(AuthInput::KEY_NAME) ?>" placeholder="username" required><br>
    <?php endif; ?>

    <input type="email" name="<?= e(AuthInput::KEY_MAIL) ?>" placeholder="email" required><br>
    <input type="password" name="<?= e(AuthInput::KEY_PASS) ?>" placeholder="password" required><br>

    <button><?= e($title) ?></button>
</form>

<p>
    <a href="<?= e($toggleUrl) ?>"><?= e($toggleText) ?></a>
</p>

<?php

$content = ob_get_clean();
require __DIR__ . '/layouts/default.php';
