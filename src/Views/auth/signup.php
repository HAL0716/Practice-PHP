<h2><?= \App\Core\Html::escape($title) ?></h2>

<?php if ($error !== null): ?>
    <p style="color:red;"><?= \App\Core\Html::escape($error) ?></p>
<?php endif; ?>

<form action="<?= \App\Core\Html::escape($actionUrl) ?>" method="post">
    <input type="hidden" name="<?= \App\Core\Html::escape(\App\Forms\SignupForm::TOKEN) ?>" value="<?= \App\Core\Html::escape($token) ?>">

    <table>
        <tr>
            <td>ユーザー名</td>
            <td><input type="text" name="<?= \App\Core\Html::escape(\App\Forms\SignupForm::NAME) ?>" value="<?= \App\Core\Html::escape($old[\App\Forms\SignupForm::NAME] ?? '') ?>" required></td>
        </tr>
        <tr>
            <td>メールアドレス</td>
            <td><input type="email" name="<?= \App\Core\Html::escape(\App\Forms\SignupForm::MAIL) ?>" value="<?= \App\Core\Html::escape($old[\App\Forms\SignupForm::MAIL] ?? '') ?>" required></td>
        </tr>
        <tr>
            <td>パスワード</td>
            <td><input type="password" name="<?= \App\Core\Html::escape(\App\Forms\SignupForm::PASS) ?>" required></td>
        </tr>
        <tr>
            <td>パスワード（確認）</td>
            <td><input type="password" name="<?= \App\Core\Html::escape(\App\Forms\SignupForm::PASS_CONFIRM) ?>" required></td>
        </tr>
        <tr>
            <td colspan="2" style="text-align: center;">
                <input type="submit" value="サインアップ">
            </td>
        </tr>
    </table>
</form>

<p>
    <a href="<?= \App\Core\Html::escape(\App\Constants\Routes::SIGNIN) ?>">サインインはこちら</a>
</p>
