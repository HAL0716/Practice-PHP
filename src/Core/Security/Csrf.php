<?php

declare(strict_types=1);

namespace App\Core\Security;

final class Csrf
{
    private const TOKEN_LENGTH = 32;
    private const TOKEN_MINUTE = 60;
    private const TOKEN_TIMEOUT = self::TOKEN_MINUTE * 60;

    private const SESSION_TOKEN = 'csrf_token';
    private const SESSION_TIME  = 'csrf_token_time';

    private function __construct()
    {
        throw new \LogicException(
            'Cannot instantiate ' . static::class
        );
    }

    public static function token(): string
    {
        if (self::isExpired()) {
            self::clear();
        }

        $token = \App\Core\Http\Session::get(self::SESSION_TOKEN);

        if (!$token) {
            $token = self::generateToken();

            \App\Core\Http\Session::set(self::SESSION_TOKEN, $token);
            \App\Core\Http\Session::set(self::SESSION_TIME, time());
        }

        return $token;
    }

    public static function verify(string $token): bool
    {
        if (self::isExpired()) {
            self::clear();
            return false;
        }

        $sessionToken = \App\Core\Http\Session::get(self::SESSION_TOKEN);

        if (!$sessionToken) {
            return false;
        }

        $valid = hash_equals($sessionToken, $token);

        if ($valid) {
            self::clear();
        }

        return $valid;
    }

    private static function generateToken(): string
    {
        return bin2hex(random_bytes(self::TOKEN_LENGTH));
    }

    private static function isExpired(): bool
    {
        $tokenTime = \App\Core\Http\Session::get(self::SESSION_TIME);

        if (!$tokenTime) {
            return true;
        }

        return (time() - $tokenTime) > self::TOKEN_TIMEOUT;
    }

    private static function clear(): void
    {
        \App\Core\Http\Session::remove(self::SESSION_TOKEN);
        \App\Core\Http\Session::remove(self::SESSION_TIME);
    }
}
