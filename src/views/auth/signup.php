<h2><?= Html::escape($title) ?></h2>

<?php if ($error !== null): ?>
    <p style="color:red;"><?= Html::escape($error) ?></p>
<?php endif; ?>

<form action="<?= Html::escape($actionUrl) ?>" method="post">
    <input type="hidden" name="<?= Html::escape(SignupForm::TOKEN) ?>" value="<?= Html::escape($token) ?>">

    <table>
        <tr>
            <td>ユーザー名</td>
            <td><input type="text" name="<?= Html::escape(SignupForm::NAME) ?>" value="<?= Html::escape($old[SignupForm::NAME] ?? '') ?>" required></td>
        </tr>
        <tr>
            <td>メールアドレス</td>
            <td><input type="email" name="<?= Html::escape(SignupForm::MAIL) ?>" value="<?= Html::escape($old[SignupForm::MAIL] ?? '') ?>" required></td>
        </tr>
        <tr>
            <td>パスワード</td>
            <td><input type="password" name="<?= Html::escape(SignupForm::PASS) ?>" required></td>
        </tr>
        <tr>
            <td>パスワード（確認）</td>
            <td><input type="password" name="<?= Html::escape(SignupForm::PASS_CONFIRM) ?>" required></td>
        </tr>
        <tr>
            <td colspan="2" style="text-align: center;">
                <input type="submit" value="サインアップ">
            </td>
        </tr>
    </table>
</form>

<p>
    <a href="<?= Html::escape(Routes::SIGNIN) ?>">サインインはこちら</a>
</p>
