<h2><?= Html::escape($title) ?></h2>

<?php if ($error !== null): ?>
    <p style="color:red;"><?= Html::escape($error) ?></p>
<?php endif; ?>

<form action="<?= Html::escape($actionUrl) ?>" method="post">
    <input type="hidden" name="<?= Html::escape(FormFields::TOKEN) ?>" value="<?= Html::escape($token) ?>">
    <input type="hidden" name="<?= Html::escape(FormFields::PASS_CURRENT) ?>">

    <table>
        <tr>
            <td>ユーザー名</td>
            <td><input type="text" name="<?= Html::escape(FormFields::NAME) ?>" value="<?= Html::escape($user[User::FIELD_USERNAME]) ?>"></td>
        </tr>
        <tr>
            <td>メールアドレス</td>
            <td><input type="email" name="<?= Html::escape(FormFields::MAIL) ?>" value="<?= Html::escape($user[User::FIELD_EMAIL]) ?>"></td>
        </tr>
        <tr>
            <td>新しいパスワード</td>
            <td>
                <input type="password" name="<?= Html::escape(FormFields::PASS) ?>"><br>
                <input type="password" name="<?= Html::escape(FormFields::PASS_CONFIRM) ?>" placeholder="確認用">
            </td>
        </tr>

        <tr>
            <td colspan="2" style="text-align: center;">
                <button type="button" class="open-modal">アップデート</button>
            </td>
        </tr>
    </table>
</form>

<p><a href="<?= Html::escape(Routes::SIGNOUT) ?>">サインアウトはこちら</a></p>

<div class="modal">
    <div class="modal-content">
        現在のパスワード
        <input type="password" class="current-password">
        <button class="submit">OK</button>
    </div>
</div>

<script>
(() => {

    const form  = document.querySelector("form");
    const modal = document.querySelector(".modal");

    const openBtn   = document.querySelector(".open-modal");
    const submitBtn = modal.querySelector(".submit");

    const modalPassword  = modal.querySelector(".current-password");
    const hiddenPassword = form.elements["<?= FormFields::PASS_CURRENT ?>"];

    const open = () => {
        modal.classList.add("is-open");
        modalPassword.focus();
    };

    const close = () => {
        modal.classList.remove("is-open");
        modalPassword.value = "";
    };

    openBtn.addEventListener("click", open);

    submitBtn.addEventListener("click", () => {

        if (!modalPassword.value.trim()) {
            alert("現在のパスワードを入力してください");
            return;
        }

        hiddenPassword.value = modalPassword.value;
        close();
        form.submit();
    });

    modal.addEventListener("click", e => {
        if (e.target === modal) close();
    });

})();
</script>
