<?php

declare(strict_types=1);

require __DIR__ . '/db.php';

$dbh = getDb();

// POSTデータ取得 & バリデーション
$name = trim($_POST['name'] ?? '');
$mail = trim($_POST['mail'] ?? '');
$pass = $_POST['pass'] ?? '';

if ($name === '' || $mail === '' || $pass === '') {
    $_SESSION['error'] = 'すべての項目を入力してください';
    header('Location: /auth?action=register');
    exit;
}

// パスワードハッシュ化
$hash = password_hash($pass, PASSWORD_DEFAULT);

// メールアドレス重複チェック
$stmt = $dbh->prepare('SELECT 1 FROM users WHERE email = ?');
$stmt->execute([$mail]);
if ($stmt->fetchColumn()) {
    $_SESSION['error'] = '同じメールアドレスが存在します';
    header('Location: /auth?action=register');
    exit;
}

// 登録処理
$stmt = $dbh->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
$stmt->execute([$name, $mail, $hash]);

// 成功 → そのままログイン処理へ
$_POST['email'] = $mail;
$_POST['password'] = $pass; // プレーンパスワードをそのままセット
require __DIR__ . '/login.php';
exit;
