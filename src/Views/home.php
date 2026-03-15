<?php

declare(strict_types=1);

use App\Constants\Routes;
use App\Core\Html;
use App\Forms\PostForm;
use App\Forms\DeletePostForm;

?>

<h2><?= Html::escape('ホーム') ?></h2>

<p><a href="<?= Html::escape(Routes::MYPAGE) ?>">マイページはこちら</a></p>

<?php if (empty($posts)) : ?>
    <p>投稿がありません。</p>
<?php else : ?>
    <table>
        <?php foreach ($posts as $post) : ?>
            <tr>
                <td><?= Html::escape($post->username() ?? '匿名') ?></td>
                <td><?= Html::escape($post->comment()) ?></td>
                <td>
                    <?php if ($post->userId() === $user_id) : ?>
                            <form action="<?= Html::escape(DeletePostForm::ACTION_URL) ?>" method="post">
                                <input type="hidden" name="<?= Html::escape(DeletePostForm::TOKEN) ?>" value="<?= Html::escape($token) ?>">
                                <input type="hidden" name="<?= Html::escape(DeletePostForm::ID) ?>" value="<?= Html::escape($post->id()) ?>">
                                <button>削除</button>
                            </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>

<form action="<?= Html::escape(PostForm::ACTION_URL) ?>" method="post">
    <input type="hidden" name="<?= Html::escape(PostForm::TOKEN) ?>" value="<?= Html::escape($token) ?>">
    <textarea name="<?= Html::escape(PostForm::COMMENT) ?>" required><?= Html::escape($old[PostForm::COMMENT] ?? '') ?></textarea>
    <button type="submit">投稿する</button>
</form>

<script>
window.addEventListener("load", function () {
    const posts = document.getElementById("posts");
    if (posts) {
        posts.scrollTop = posts.scrollHeight;
    }
});
</script>
