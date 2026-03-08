<h2><?= \App\Core\Html::escape($title) ?></h2>

<p><a href="<?= \App\Core\Html::escape(\App\Constants\Routes::MYPAGE) ?>">マイページはこちら</a></p>

<?php if (empty($posts)) : ?>
    <p>投稿がありません。</p>
<?php else : ?>
    <div id="posts" style="max-height: 300px; width: 300px; overflow-y: auto; border: 1px solid #000; padding: 10px;">
        <table>
            <?php foreach ($posts as $post) : ?>
                <tr>
                    <?php if ($post->userId() === \App\Core\Session::userId()) : ?>
                        <td></td>
                        <td><?= \App\Core\Html::escape($post->comment()) ?></td>
                        <td><?= \App\Core\Html::escape($post->username()) ?></td>
                    <?php else : ?>
                        <td><?= \App\Core\Html::escape($post->username() ?? '匿名') ?></td>
                        <td><?= \App\Core\Html::escape($post->comment()) ?></td>
                        <td></td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
<?php endif; ?>

<form action="<?= \App\Core\Html::escape(\App\Constants\Routes::HOME) ?>" method="post">
    <input type="hidden" name="<?= \App\Core\Html::escape(\App\Forms\PostForm::TOKEN) ?>" value="<?= \App\Core\Html::escape($token) ?>">
    <textarea name="<?= \App\Core\Html::escape(\App\Forms\PostForm::COMMENT) ?>" required><?= \App\Core\Html::escape($old[\App\Forms\PostForm::COMMENT] ?? '') ?></textarea>
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
