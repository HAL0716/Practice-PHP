<?php
declare(strict_types=1);

use App\Constants\Routes;
use App\Core\Html;
use App\Forms\PostForm;

?>

<h2><?= Html::escape('ホーム') ?></h2>

<p><a href="<?= Html::escape(Routes::MYPAGE) ?>">マイページはこちら</a></p>

<?php if (empty($posts)) : ?>
    <p>投稿がありません。</p>
<?php else : ?>
    <div id="posts" style="max-height: 300px; width: 300px; overflow-y: auto; border: 1px solid #000; padding: 10px;">
        <table>
            <?php foreach ($posts as $post) : ?>
                <tr>
                    <?php if ($post->userId() === $user_id) : ?>
                        <td></td>
                        <td><?= Html::escape($post->comment()) ?></td>
                        <td><?= Html::escape($post->username()) ?></td>
                    <?php else : ?>
                        <td><?= Html::escape($post->username() ?? '匿名') ?></td>
                        <td><?= Html::escape($post->comment()) ?></td>
                        <td></td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
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
