<h2><?= Html::escape($title) ?></h2>

<p><a href="<?= Html::escape(Routes::MYPAGE) ?>">マイページはこちら</a></p>

<?php if (empty($posts)) : ?>
    <p>投稿がありません。</p>
<?php else : ?>
    <table>
        <?php foreach ($posts as $post) { ?>
            <tr>
                <td><?= Html::escape($post[POST::FIELD_USERNAME]) ?></td>
                <td><?= Html::escape($post[POST::FIELD_COMMENT]) ?></td>
            </tr>
        <?php } ?>
    </table>
<?php endif; ?>

<form action="<?= Html::escape(Routes::POST_CREATE) ?>" method="post">
    <textarea name="comment" required></textarea>
    <button type="submit">投稿する</button>
</form>
