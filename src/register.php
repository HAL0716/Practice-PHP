<?php

declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth_input.php';

$dbh = getDb();

// POSTデータ取得 & バリデーション
$input = new AuthInput($_POST);
if (!$input->validate(true)) {
    $_SESSION['error'] = 'すべての項目を入力してください';
    header('Location: /auth?action=register');
    exit;
}

// パスワードハッシュ化
$hash = password_hash($input->pass, PASSWORD_DEFAULT);

// メールアドレス重複チェック
$stmt = $dbh->prepare('SELECT 1 FROM users WHERE email = ?');
$stmt->execute([$input->mail]);
if ($stmt->fetchColumn()) {
    $_SESSION['error'] = '同じメールアドレスが存在します';
    header('Location: /auth?action=register');
    exit;
}

// 登録処理
$stmt = $dbh->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
$stmt->execute([$input->name, $input->mail, $hash]);

// 成功 → そのままログイン処理へ
$_POST[AuthInput::KEY_MAIL] = $input->mail;
$_POST[AuthInput::KEY_PASS] = $input->pass;
require_once __DIR__ . '/login.php';
exit;
