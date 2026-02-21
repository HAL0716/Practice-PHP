<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($title ?? '', ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="icon" href="data:,">
</head>
<body>
    <?= $content ?? '' ?>
</body>
</html>
