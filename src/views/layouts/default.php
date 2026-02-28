<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title><?= Html::escape($title ?? '') ?></title>
    <link rel="icon" href="data:,">
</head>
<style>
.modal {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.4);

    justify-content: center;
    align-items: center;
}

.modal.is-open {
    display: flex;
}

.modal-content {
    background: #fff;
    padding: 20px;
    min-width: 300px;
}
</style>
<body>
    <?= $content ?? '' ?>
</body>
</html>
