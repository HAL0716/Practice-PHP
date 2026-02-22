<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/auth_input.php';

CsrfToken::verify(); // CSRFトークン検証

$db = Database::connect();

// ログイン失敗回数の制限設定
const MAX_LOGIN_ATTEMPTS = 5;
const LOGIN_ATTEMPT_TIMEOUT = 15 * 60; // 15分

// セッションの初期化
$loginAttempts = (int) Session::get('login_attempts', 0);
$lastAttemptTime = (int) Session::get('login_attempt_time', 0);
$currentTime = time();

// タイムアウト確認（最後の試行から15分以上経過したなら失敗回数をリセット）
if ($lastAttemptTime > 0 && $currentTime - $lastAttemptTime > LOGIN_ATTEMPT_TIMEOUT) {
    $loginAttempts = 0;
    Session::remove('login_attempts');
    Session::remove('login_attempt_time');
}

// ログイン試行回数チェック
if ($loginAttempts >= MAX_LOGIN_ATTEMPTS) {
    Session::flash('error', 'ログイン試行回数が上限に達しました。15分後にお試しください');
    header('Location: /auth?action=login');
    exit;
}

// POSTデータ取得 & バリデーション
$input = new AuthInput();
if (!$input->validate(false)) {
    Session::flash('error', 'すべての項目を入力してください');
    header('Location: /auth?action=login');
    exit;
}

// ユーザ存在チェック
$stmt = $db->prepare('SELECT id, name, password FROM users WHERE email = ?');
$stmt->execute([$input->mail]);
if (!$user = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $input->pass = ''; // タイミング攻撃対策
}

// パスワード検証
if (!isset($user['password']) || !password_verify($input->pass, $user['password'])) {
    // ログイン失敗時：失敗回数をインクリメント
    $loginAttempts++;
    Session::set('login_attempts', $loginAttempts);
    Session::set('login_attempt_time', $currentTime);

    $remainingAttempts = MAX_LOGIN_ATTEMPTS - $loginAttempts;
    if ($remainingAttempts > 0) {
        Session::flash('error', "メールアドレスまたはパスワードが違います（残り試行回数: {$remainingAttempts}）");
    } else {
        Session::flash('error', 'ログイン試行回数が上限に達しました。15分後にお試しください');
    }

    header('Location: /auth?action=login');
    exit;
} else {
    // ログイン成功：失敗回数をリセット
    Session::regenerate(); // 固定化攻撃対策
    Session::remove('login_attempts');
    Session::remove('login_attempt_time');

    Session::set('user_id', $user['id']);
    Session::set('user_name', $user['name']);
    header('Location: /home');
    exit;
}
