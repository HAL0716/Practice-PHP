<?php
declare(strict_types=1);

use App\Application\Constants\Routes;
use App\Application\Forms\User\UpdateForm;
use App\Application\Forms\User\DeleteForm;
use App\Support\Html;

?>

<h2><?= Html::escape('マイページ') ?></h2>

<?php if ($error !== null): ?>
    <p style="color:red;"><?= Html::escape($error) ?></p>
<?php endif; ?>

<p><a href="<?= Html::escape(Routes::POST_HOME) ?>">ホームへ戻る</a></p>

<form action="<?= Html::escape(UpdateForm::ACTION_URL) ?>" method="post" class="update-form">
    <input type="hidden" name="<?= Html::escape(UpdateForm::TOKEN) ?>" value="<?= Html::escape($token) ?>">
    <input type="hidden" name="<?= Html::escape(UpdateForm::PASS_CURRENT) ?>">

    <table>
        <tr>
            <td>ユーザー名</td>
            <td><input type="text" name="<?= Html::escape(UpdateForm::NAME) ?>" value="<?= Html::escape($user->username()) ?>"></td>
        </tr>
        <tr>
            <td>メールアドレス</td>
            <td><input type="email" name="<?= Html::escape(UpdateForm::MAIL) ?>" value="<?= Html::escape($user->email()) ?>"></td>
        </tr>
        <tr>
            <td>新しいパスワード</td>
            <td>
                <input type="password" name="<?= Html::escape(UpdateForm::PASS) ?>"><br>
                <input type="password" name="<?= Html::escape(UpdateForm::PASS_CONFIRM) ?>" placeholder="確認用">
            </td>
        </tr>

        <tr>
            <td colspan="2" style="text-align: center;">
                <button type="button" class="open-modal-update">アップデート</button>
            </td>
        </tr>
    </table>
</form>

<form action="<?= Html::escape(DeleteForm::ACTION_URL) ?>" method="post" class="delete-form">
    <input type="hidden" name="<?= Html::escape(DeleteForm::TOKEN) ?>" value="<?= Html::escape($token) ?>">
    <input type="hidden" name="<?= Html::escape(DeleteForm::PASS_CURRENT) ?>">
    <button type="button" class="open-modal-delete" style="color: red;">アカウント削除</button>
</form>

<p><a href="<?= Html::escape(Routes::USER_SIGNOUT) ?>">サインアウトはこちら</a></p>

<div class="modal">
    <div class="modal-content">
        現在のパスワード
        <input type="password" class="current-password">
        <button class="submit">OK</button>
    </div>
</div>

<script>
(() => {

    const updateForm = document.querySelector(".update-form");
    const deleteForm = document.querySelector(".delete-form");

    const modal = document.querySelector(".modal");

    const openUpdateBtn = document.querySelector(".open-modal-update");
    const openDeleteBtn = document.querySelector(".open-modal-delete");

    const submitBtn = modal.querySelector(".submit");
    const modalPassword = modal.querySelector(".current-password");

    let currentForm = null;
    let passwordField = null;

    const open = (form, fieldName) => {
        currentForm = form;
        passwordField = form.elements[fieldName];

        modal.classList.add("is-open");
        modalPassword.focus();
    };

    const close = () => {
        modal.classList.remove("is-open");
        modalPassword.value = "";
    };

    openUpdateBtn.addEventListener("click", () => {
        open(updateForm, "<?= Html::escape(UpdateForm::PASS_CURRENT) ?>");
    });

    openDeleteBtn.addEventListener("click", () => {
        open(deleteForm, "<?= Html::escape(DeleteForm::PASS_CURRENT) ?>");
    });

    submitBtn.addEventListener("click", () => {

        if (!modalPassword.value.trim()) {
            alert("現在のパスワードを入力してください");
            return;
        }

        passwordField.value = modalPassword.value;
        close();
        currentForm.submit();
    });

    modal.addEventListener("click", e => {
        if (e.target === modal) close();
    });

})();
</script>
