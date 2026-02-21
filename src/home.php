<?php

declare(strict_types=1);

ob_start();

$title = 'ホーム';
?>

<h2><?= e($title) ?></h2>

<?php if (isset($_SESSION['user_name'])): ?>
    <p>ようこそ、<?= e($_SESSION['user_name']) ?>さん！</p>

    <p><a href="/logout">ログアウト</a></p>
<?php else: ?>
    <p><a href="/auth?action=login">ログイン</a> または <a href="/auth?action=register">新規作成</a> してください。</p>
<?php endif; ?>

<?php

$content = ob_get_clean();
require __DIR__ . '/layouts/default.php';
