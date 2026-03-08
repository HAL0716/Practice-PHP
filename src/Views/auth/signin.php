<h2><?= Html::escape($title) ?></h2>

<?php if ($error !== null): ?>
    <p style="color:red;"><?= Html::escape($error) ?></p>
<?php endif; ?>

<form action="<?= Html::escape($actionUrl) ?>" method="post">
    <input type="hidden" name="<?= Html::escape(SigninForm::TOKEN) ?>" value="<?= Html::escape($token) ?>">

    <table>
        <tr>
            <td>メールアドレス</td>
            <td><input type="email" name="<?= Html::escape(SigninForm::MAIL) ?>" value="<?= Html::escape($old[SigninForm::MAIL] ?? '') ?>" required></td>
        </tr>
        <tr>
            <td>パスワード</td>
            <td><input type="password" name="<?= Html::escape(SigninForm::PASS) ?>" required></td>
        </tr>
        <tr>
            <td colspan="2" style="text-align: center;">
                <input type="submit" value="サインイン">
            </td>
        </tr>
    </table>
</form>

<p>
    <a href="<?= Html::escape(Routes::SIGNUP) ?>">サインアップはこちら</a>
</p>
