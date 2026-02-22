<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/auth_input.php';

CsrfToken::verify(); // CSRFトークン検証

$db = Database::connect();

// POSTデータ取得 & バリデーション
$input = new AuthInput();
if (!$input->validate(true)) {
    Session::flash('error', 'すべての項目を入力してください');
    header('Location: /auth?action=register');
    exit;
}

// パスワードハッシュ化
$hash = password_hash($input->pass, PASSWORD_DEFAULT);

// メールアドレス重複チェック
$stmt = $db->prepare('SELECT 1 FROM users WHERE email = ?');
$stmt->execute([$input->mail]);
if ($stmt->fetchColumn()) {
    Session::flash('error', 'このメールアドレスは既に登録されています');
    header('Location: /auth?action=register');
    exit;
}

// 登録処理
$stmt = $db->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
$stmt->execute([$input->name, $input->mail, $hash]);

// 成功 → ログイン状態を作成してホームへ
Session::regenerate(); // 固定化攻撃対策
Session::set('user_id', $db->lastInsertId());
Session::set('user_name', $input->name);
header('Location: /home');
exit;
