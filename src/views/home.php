<h2><?= Html::escape($title) ?></h2>

<?php if ($username): ?>
    <p>ようこそ、<?= Html::escape($username) ?>さん！</p>

    <p><a href="/logout">ログアウト</a></p>
<?php else: ?>
    <p><a href="/auth?action=login">ログイン</a> または <a href="/auth?action=register">新規作成</a> してください。</p>
<?php endif; ?>
