<h2><?= Html::escape($title) ?></h2>

<p><a href="<?= Html::escape(Routes::MYPAGE) ?>">マイページはこちら</a></p>

<?php if (empty($posts)) : ?>
    <p>投稿がありません。</p>
<?php else : ?>
    <div id="posts" style="max-height: 300px; width: 300px; overflow-y: auto; border: 1px solid #000; padding: 10px;">
        <table>
            <?php foreach ($posts as $post) : ?>
                <tr>
                    <?php if ($post[Post::FIELD_USER_ID] === Session::get(SessionKeys::USER_ID)) : ?>
                        <td></td>
                        <td><?= Html::escape($post[Post::FIELD_COMMENT]) ?></td>
                        <td><?= Html::escape($post[Post::FIELD_USERNAME]) ?></td>
                    <?php else : ?>
                        <td><?= Html::escape($post[Post::FIELD_USERNAME] ?? '匿名') ?></td>
                        <td><?= Html::escape($post[Post::FIELD_COMMENT]) ?></td>
                        <td></td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
<?php endif; ?>

<form action="<?= Html::escape(Routes::POST_CREATE) ?>" method="post">
    <input type="hidden" name="<?= Html::escape(FormFields::TOKEN) ?>" value="<?= Html::escape($token) ?>">
    <textarea name="comment" required></textarea>
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
