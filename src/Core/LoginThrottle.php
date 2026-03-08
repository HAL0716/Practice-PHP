<?php

declare(strict_types=1);

namespace App\Core;

final class LoginThrottle
{
    private const MAX_ATTEMPTS = 5;
    private const LOCK_MINUTES = 15;
    private const LOCK_TIMEOUT = self::LOCK_MINUTES * 60;

    private const SESSION_ATTEMPTS = 'login_attempts';
    private const SESSION_TIME     = 'login_attempt_time';

    public static function isLocked(): bool
    {
        $attempts = (int) \App\Core\Session::get(self::SESSION_ATTEMPTS, 0);
        $last     = (int) \App\Core\Session::get(self::SESSION_TIME, 0);

        if ($last && time() - $last > self::LOCK_TIMEOUT) {
            self::clear();
            return false;
        }

        return $attempts >= self::MAX_ATTEMPTS;
    }

    public static function hit(): bool
    {
        $attempts = (int) \App\Core\Session::get(self::SESSION_ATTEMPTS, 0) + 1;

        \App\Core\Session::set(self::SESSION_ATTEMPTS, $attempts);
        \App\Core\Session::set(self::SESSION_TIME, time());

        return $attempts >= self::MAX_ATTEMPTS;
    }

    public static function clear(): void
    {
        \App\Core\Session::remove(self::SESSION_ATTEMPTS);
        \App\Core\Session::remove(self::SESSION_TIME);
    }
}
