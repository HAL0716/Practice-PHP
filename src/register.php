<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/auth_input.php';
require_once __DIR__ . '/models/User.php';

// CSRFトークン検証
$token = Request::post('token');
if (!Csrf::verify($token)) {
    Session::flash('error', '不正なリクエストです');
    header('Location: /auth?action=register');
    exit;
}

// POSTデータ取得 & バリデーション
$input = new AuthInput();
if (!$input->validate(true)) {
    Session::flash('error', 'すべての項目を入力してください');
    header('Location: /auth?action=register');
    exit;
}

// メールアドレス重複チェック
if (User::findByEmail($input->mail)) {
    Session::flash('error', 'このメールアドレスは既に登録されています');
    header('Location: /auth?action=register');
    exit;
}

// 登録処理
if (!User::create($input->name, $input->mail, $input->pass)) {
    Session::flash('error', 'ユーザーの登録に失敗しました');
    header('Location: /auth?action=register');
    exit;
}

// 成功 → ログイン状態を作成してホームへ
Session::regenerate(); // 固定化攻撃対策
Session::set('user_id', User::findByEmail($input->mail)['id']);
Session::set('user_name', $input->name);
header('Location: /home');
exit;
