<?php

declare(strict_types=1);

/**
 * 配列から文字列を安全に取得する
 *
 * 指定キーが存在しない、文字列でない、
 * または許可リストに含まれていない場合はデフォルト値を返す。
 *
 * @param array  $source       取得元の配列（$_GET, $_POST 等）
 * @param string $key          配列のキー
 * @param string $default      デフォルト値
 * @param array  $allowed      許可する文字列の配列（空なら制限なし）
 * @return string
 */
function getStr(array $source, string $key, string $default = '', array $allowed = []): string
{
    $value = $source[$key] ?? $default;

    if (!is_string($value)) {
        return $default;
    }

    if ($allowed !== [] && !in_array($value, $allowed, true)) {
        return $default;
    }

    return $value;
}

function e(
    string $string,
    int $flags = ENT_QUOTES,
    ?string $encoding = 'UTF-8',
    bool $double_encode = true
): string {
    return htmlspecialchars($string, $flags, $encoding, $double_encode);
}

function setToken(): void
{
    if (empty($_SESSION['token'])) {
        $_SESSION['token'] = bin2hex(random_bytes(32));
    }
}

function checkToken(): void
{
    if (!isset($_SESSION['token'], $_POST['token']) || !hash_equals($_SESSION['token'], $_POST['token'])) {
        $_SESSION['error'] = '不正なリクエストです';
        header('Location: /auth');
        exit;
    }

    unset($_SESSION['token']);
}
