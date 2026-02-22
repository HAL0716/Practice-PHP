<?php

declare(strict_types=1);

/**
 * セッション管理
 */
final class SessionManager
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/',
                'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            session_start();
        }
    }

    public static function set(string $key, mixed $value): void
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        self::start();
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void
    {
        self::start();
        unset($_SESSION[$key]);
    }

    public static function destroy(): void
    {
        self::start();
        $_SESSION = array();
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        session_destroy();
    }
}

/**
 * 配列値の安全な取得・設定
 */
final class ArrayHelper
{
    public static function getString(
        array $source,
        string $key,
        string $default = '',
        array $allowed = []
    ): string {
        $value = $source[$key] ?? $default;

        if (!is_string($value)) {
            return $default;
        }

        if ($allowed !== [] && !in_array($value, $allowed, true)) {
            return $default;
        }

        return $value;
    }
}

/**
 * CSRF トークン管理
 */
final class CsrfToken
{
    private const TOKEN_LENGTH = 32;
    private const SESSION_KEY = 'token';
    private const POST_KEY = 'token';
    private const ERROR_MESSAGE = '不正なリクエストです';
    private const REDIRECT_URL = '/auth';

    public static function generate(): void
    {
        SessionManager::start();
        if (empty(SessionManager::get(self::SESSION_KEY))) {
            SessionManager::set(
                self::SESSION_KEY,
                bin2hex(random_bytes(self::TOKEN_LENGTH))
            );
        }
    }

    public static function verify(): void
    {
        if (!self::isValid()) {
            FlashMessage::setError(self::ERROR_MESSAGE);
            header('Location: ' . self::REDIRECT_URL);
            exit;
        }
        self::consume();
    }

    private static function isValid(): bool
    {
        return SessionManager::has(self::SESSION_KEY)
            && isset($_POST[self::POST_KEY])
            && hash_equals(
                SessionManager::get(self::SESSION_KEY),
                $_POST[self::POST_KEY]
            );
    }

    private static function consume(): void
    {
        SessionManager::remove(self::SESSION_KEY);
    }
}

/**
 * フラッシュメッセージ管理
 */
final class FlashMessage
{
    private const SESSION_KEY = 'error';

    public static function setError(string $message): void
    {
        SessionManager::set(self::SESSION_KEY, $message);
    }

    public static function getError(): ?string
    {
        $message = SessionManager::get(self::SESSION_KEY);

        if ($message !== null) {
            SessionManager::remove(self::SESSION_KEY);
        }

        return $message;
    }
}
