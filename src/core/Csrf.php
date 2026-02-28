<?php

declare(strict_types=1);

final class Csrf
{
    private const TOKEN_LENGTH  = 32;
    private const TOKEN_MINUTE  = 60;
    private const TOKEN_TIMEOUT = self::TOKEN_MINUTE * 60;

    private function __construct()
    {
    }

    public static function token(): string
    {
        if (self::isExpired()) {
            self::clear();
        }

        $token = Session::get(SessionKeys::CSRF_TOKEN);

        if (!$token) {
            $token = self::generateToken();
            Session::set(SessionKeys::CSRF_TOKEN, $token);
            Session::set(SessionKeys::CSRF_TOKEN_TIME, time());
        }

        return $token;
    }

    public static function verify(string $token): bool
    {
        if (self::isExpired()) {
            self::clear();
            return false;
        }

        $sessionToken = Session::get(SessionKeys::CSRF_TOKEN);

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
        $tokenTime = Session::get(SessionKeys::CSRF_TOKEN_TIME);

        if (!$tokenTime) {
            return true;
        }

        return (time() - $tokenTime) > self::TOKEN_TIMEOUT;
    }

    private static function clear(): void
    {
        Session::remove(SessionKeys::CSRF_TOKEN);
        Session::remove(SessionKeys::CSRF_TOKEN_TIME);
    }
}
