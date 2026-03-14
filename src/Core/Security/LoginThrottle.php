<?php

declare(strict_types=1);

namespace App\Core\Security;

use App\Core\Http\Session;

final class LoginThrottle
{
    private const MAX_ATTEMPTS = 5;
    private const LOCK_MINUTES = 15;
    private const LOCK_TIMEOUT = self::LOCK_MINUTES * 60;

    private const SESSION_ATTEMPTS = 'login_attempts';
    private const SESSION_TIME     = 'login_attempt_time';

    private const ERROR_LOCKED = 'ログイン試行回数が上限に達しました。しばらくしてから再度お試しください';

    private function __construct()
    {
        throw new \LogicException(
            'Cannot instantiate ' . static::class
        );
    }

    public static function isLocked(): bool
    {
        $attempts = (int) Session::get(self::SESSION_ATTEMPTS, 0);
        $last     = (int) Session::get(self::SESSION_TIME, 0);

        if ($last && time() - $last > self::LOCK_TIMEOUT) {
            self::clear();
            return false;
        }

        return $attempts >= self::MAX_ATTEMPTS;
    }

    public static function hit(): ?string
    {
        $attempts = (int) Session::get(self::SESSION_ATTEMPTS, 0) + 1;

        Session::set(self::SESSION_ATTEMPTS, $attempts);
        Session::set(self::SESSION_TIME, time());

        return $attempts >= self::MAX_ATTEMPTS ? self::ERROR_LOCKED : null;
    }

    public static function clear(): void
    {
        Session::remove(self::SESSION_ATTEMPTS);
        Session::remove(self::SESSION_TIME);
    }
}
