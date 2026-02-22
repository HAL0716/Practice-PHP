<?php

declare(strict_types=1);

final class Csrf
{
    private const TOKEN_KEY      = 'csrf_token';
    private const TOKEN_TIME_KEY = 'csrf_token_time';
    private const TOKEN_LENGTH   = 32;
    private const TOKEN_TIMEOUT  = 3600; // seconds

    private function __construct()
    {
    }

    public static function token(): string
    {
        if (self::isExpired()) {
            self::clear();
        }

        $token = Session::get(self::TOKEN_KEY);

        if (!$token) {
            $token = self::generateToken();
            Session::set(self::TOKEN_KEY, $token);
            Session::set(self::TOKEN_TIME_KEY, time());
        }

        return $token;
    }

    public static function verify(string $token): bool
    {
        if (self::isExpired()) {
            self::clear();
            return false;
        }

        $sessionToken = Session::get(self::TOKEN_KEY);

        if (!$sessionToken) {
            return false;
        }

        $valid = hash_equals($sessionToken, $token);

        // ワンタイムトークン
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
        $tokenTime = Session::get(self::TOKEN_TIME_KEY);

        if (!$tokenTime) {
            return true;
        }

        return (time() - $tokenTime) > self::TOKEN_TIMEOUT;
    }

    private static function clear(): void
    {
        Session::remove(self::TOKEN_KEY);
        Session::remove(self::TOKEN_TIME_KEY);
    }
}
