<h2><?= Html::escape($title) ?></h2>

<?php if ($error !== null): ?>
    <p style="color:red;"><?= Html::escape($error) ?></p>
<?php endif; ?>

<form action="<?= Html::escape($actionUrl) ?>" method="post">
    <input type="hidden" name="<?= Html::escape(FormFields::TOKEN) ?>" value="<?= Html::escape($token) ?>">

    <table>
        <tr>
            <td>ユーザー名</td>
            <td><input type="text" name="<?= Html::escape(FormFields::NAME) ?>" value="<?= Html::escape($old[FormFields::NAME] ?? '') ?>" required></td>
        </tr>
        <tr>
            <td>メールアドレス</td>
            <td><input type="email" name="<?= Html::escape(FormFields::MAIL) ?>" value="<?= Html::escape($old[FormFields::MAIL] ?? '') ?>" required></td>
        </tr>
        <tr>
            <td>パスワード</td>
            <td><input type="password" name="<?= Html::escape(FormFields::PASS) ?>" required></td>
        </tr>
        <tr>
            <td>パスワード（確認）</td>
            <td><input type="password" name="<?= Html::escape(FormFields::PASS_CONFIRM) ?>" required></td>
        </tr>
        <tr>
            <td colspan="2" style="text-align: center;">
                <input type="submit" value="<?= Html::escape($title) ?>">
            </td>
        </tr>
    </table>
</form>

<p>
    <a href="<?= Html::escape(Routes::SIGNIN) ?>">サインインはこちら</a>
</p>
