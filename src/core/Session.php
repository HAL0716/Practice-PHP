<?php

declare(strict_types=1);

require_once __DIR__ . '/../entities/UserEntity.php';
require_once __DIR__ . '/../constants/SessionKeys.php';

final class Session
{
    private const FLASH_PREFIX = '_flash_';

    private function __construct()
    {
        throw new LogicException(
            'Cannot instantiate ' . static::class
        );
    }

    private static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    private static function regenerate(): void
    {
        self::start();
        session_regenerate_id(true);
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

    public static function remove(string $key): void
    {
        self::start();
        unset($_SESSION[$key]);
    }

    public static function clear(): void
    {
        self::start();
        $_SESSION = [];
    }

    public static function flash(string $key, mixed $value): void
    {
        self::set(self::FLASH_PREFIX . $key, $value);
    }

    public static function getFlash(string $key, mixed $default = null): mixed
    {
        $flashKey = self::FLASH_PREFIX . $key;

        $value = self::get($flashKey, $default);
        self::remove($flashKey);

        return $value;
    }

    public static function login(UserEntity $user): void
    {
        self::regenerate();
        $_SESSION[SessionKeys::USER_ID] = $user->id();
    }

    public static function logout(): void
    {
        self::start();

        $_SESSION = [];

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

    public static function isLoggedIn(): bool
    {
        return self::userId() !== null;
    }

    public static function userId(): ?int
    {
        $id = self::get(SessionKeys::USER_ID);

        return is_int($id) ? $id : null;
    }
}
