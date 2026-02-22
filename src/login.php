<?php

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth_input.php';

CsrfToken::verify(); // CSRFトークン検証

$dbh = getDb();

// ログイン失敗回数の制限設定
const MAX_LOGIN_ATTEMPTS = 5;
const LOGIN_ATTEMPT_TIMEOUT = 15 * 60; // 15分

// セッションの初期化
$loginAttempts = (int) SessionManager::get('login_attempts', 0);
$lastAttemptTime = (int) SessionManager::get('login_attempt_time', 0);
$currentTime = time();

// タイムアウト確認（最後の試行から15分以上経過したなら失敗回数をリセット）
if ($lastAttemptTime > 0 && $currentTime - $lastAttemptTime > LOGIN_ATTEMPT_TIMEOUT) {
    $loginAttempts = 0;
    SessionManager::remove('login_attempts');
    SessionManager::remove('login_attempt_time');
}

// ログイン試行回数チェック
if ($loginAttempts >= MAX_LOGIN_ATTEMPTS) {
    FlashMessage::setError('ログイン試行回数が上限に達しました。15分後にお試しください');
    header('Location: /auth?action=login');
    exit;
}

// POSTデータ取得 & バリデーション
$input = new AuthInput($_POST);
if (!$input->validate(false)) {
    FlashMessage::setError('すべての項目を入力してください');
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
    // ログイン失敗時：失敗回数をインクリメント
    $loginAttempts++;
    SessionManager::set('login_attempts', $loginAttempts);
    SessionManager::set('login_attempt_time', $currentTime);

    $remainingAttempts = MAX_LOGIN_ATTEMPTS - $loginAttempts;
    if ($remainingAttempts > 0) {
        FlashMessage::setError("メールアドレスまたはパスワードが違います（残り試行回数: {$remainingAttempts}）");
    } else {
        FlashMessage::setError('ログイン試行回数が上限に達しました。15分後にお試しください');
    }

    header('Location: /auth?action=login');
    exit;
} else {
    // ログイン成功：失敗回数をリセット
    session_regenerate_id(true); // 固定化攻撃対策
    SessionManager::remove('login_attempts');
    SessionManager::remove('login_attempt_time');

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    header('Location: /home');
    exit;
}
