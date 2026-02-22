<h2><?= Html::escape($title) ?></h2>

<?php if ($error !== null): ?>
    <p style="color:red;"><?= Html::escape($error) ?></p>
<?php endif; ?>

<form action="/<?= Html::escape($action) ?>" method="post">
    <input type="hidden" name="action" value="<?= Html::escape($action) ?>">
    <input type="hidden" name="token"  value="<?= Html::escape($token)  ?>">

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
