<h2><?= Html::escape($title) ?></h2>

<p>ようこそ、<?= Html::escape($username) ?>さん！</p>

<p><a href="<?= Html::escape(Routes::MYPAGE) ?>">マイページはこちら</a></p>

<form action="<?= Html::escape(Routes::POST_CREATE) ?>" method="post">
    <textarea name="comment" required></textarea>
    <button type="submit">投稿する</button>
</form>
