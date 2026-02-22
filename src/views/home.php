<h2><?= Html::escape($title) ?></h2>

<?php if ($isLoggedIn): ?>
    <p>ようこそ、<?= Html::escape($username) ?>さん！</p>
    <p><a href="/signout">サインアウトはこちら</a></p>
<?php else: ?>
    <p>
        <a href="/signin">サインインはこちら</a><br>
        <a href="/signup">サインアップはこちら</a>
    </p>
<?php endif; ?>
