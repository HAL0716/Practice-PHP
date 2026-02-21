<?php

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

checkToken();

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth_input.php';

$dbh = getDb();

// POSTデータ取得 & バリデーション
$input = new AuthInput($_POST);
if (!$input->validate(false)) {
    $_SESSION['error'] = 'すべての項目を入力してください';
    header('Location: /auth?action=login');
    exit;
}

// ユーザ存在チェック
$stmt = $dbh->prepare('SELECT id, name, password FROM users WHERE email = ?');
$stmt->execute([$input->mail]);
if (!$user = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $input->pass = ''; // タイミング攻撃対策
}

// パスワード検証
if (!isset($user['password']) || !password_verify($input->pass, $user['password'])) {
    $_SESSION['error'] = 'メールアドレスまたはパスワードが違います';
    header('Location: /auth?action=login');
    exit;
} else {
    // ログイン成功
    session_regenerate_id(true); // 固定化攻撃対策
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    header('Location: /home');
    exit;
}
