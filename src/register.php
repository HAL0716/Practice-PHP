<?php

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth_input.php';

CsrfToken::verify(); // CSRFトークン検証

$dbh = getDb();

// POSTデータ取得 & バリデーション
$input = new AuthInput($_POST);
if (!$input->validate(true)) {
    FlashMessage::setError('すべての項目を入力してください');
    header('Location: /auth?action=register');
    exit;
}

// パスワードハッシュ化
$hash = password_hash($input->pass, PASSWORD_DEFAULT);

// メールアドレス重複チェック
$stmt = $dbh->prepare('SELECT 1 FROM users WHERE email = ?');
$stmt->execute([$input->mail]);
if ($stmt->fetchColumn()) {
    FlashMessage::setError('そのメールアドレスは既に使われています');
    header('Location: /auth?action=register');
    exit;
}

// 登録処理
$stmt = $dbh->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
$stmt->execute([$input->name, $input->mail, $hash]);

// 成功 → ログイン状態を作成してホームへ
session_regenerate_id(true);
$_SESSION['user_id'] = (int)$dbh->lastInsertId();
$_SESSION['user_name'] = $input->name;
header('Location: /home');
exit;
