<?php

declare(strict_types=1);

namespace App\Core\Http;

final class Session
{
    private const USER_ID = 'user_id';

    private const FLASH_PREFIX = '_flash_';
    private const FLASH_ERROR  = 'error';
    private const FLASH_OLD    = 'old';

    private function __construct()
    {
        throw new \LogicException(
            'Cannot instantiate ' . static::class
        );
    }

    public static function init(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    private static function regenerate(bool $delete_old_session = true): void
    {
        session_regenerate_id($delete_old_session);
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function clear(): void
    {
        $_SESSION = [];
    }

    public static function login(\App\Entities\UserEntity $user): void
    {
        self::regenerate();
        $_SESSION[self::USER_ID] = $user->id();
    }

    public static function logout(): void
    {
        self::clear();

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
        $id = self::get(self::USER_ID);

        return is_int($id) ? $id : null;
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

    public static function flashError(string $message): void
    {
        self::flash(self::FLASH_ERROR, $message);
    }

    public static function error(): ?string
    {
        return self::getFlash(self::FLASH_ERROR);
    }

    public static function flashOld(array $data): void
    {
        self::flash(self::FLASH_OLD, $data);
    }

    public static function old(): array
    {
        return self::getFlash(self::FLASH_OLD, []);
    }
}
