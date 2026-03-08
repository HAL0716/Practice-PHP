<h2><?= \App\Core\Html::escape($title) ?></h2>

<?php if ($error !== null): ?>
    <p style="color:red;"><?= \App\Core\Html::escape($error) ?></p>
<?php endif; ?>

<form action="<?= \App\Core\Html::escape($actionUrl) ?>" method="post">
    <input type="hidden" name="<?= \App\Core\Html::escape(\App\Forms\SigninForm::TOKEN) ?>" value="<?= \App\Core\Html::escape($token) ?>">

    <table>
        <tr>
            <td>メールアドレス</td>
            <td><input type="email" name="<?= \App\Core\Html::escape(\App\Forms\SigninForm::MAIL) ?>" value="<?= \App\Core\Html::escape($old[\App\Forms\SigninForm::MAIL] ?? '') ?>" required></td>
        </tr>
        <tr>
            <td>パスワード</td>
            <td><input type="password" name="<?= \App\Core\Html::escape(\App\Forms\SigninForm::PASS) ?>" required></td>
        </tr>
        <tr>
            <td colspan="2" style="text-align: center;">
                <input type="submit" value="サインイン">
            </td>
        </tr>
    </table>
</form>

<p>
    <a href="<?= \App\Core\Html::escape(\App\Constants\Routes::SIGNUP) ?>">サインアップはこちら</a>
</p>
