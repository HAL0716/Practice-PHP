<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use App\Contracts\Http\SessionInterface;
use App\Contracts\Security\LoginThrottleInterface;

final class LoginThrottle implements LoginThrottleInterface
{
    public const ERROR_LOCKED = 'ログイン試行回数が上限に達しました。しばらくしてから再度お試しください';

    private const MAX_ATTEMPTS = 5;
    private const LOCK_MINUTES = 15;
    private const LOCK_TIMEOUT = self::LOCK_MINUTES * 60;

    private const SESSION_ATTEMPTS = 'login_attempts';
    private const SESSION_TIME     = 'login_attempt_time';

    private $now;

    public function __construct(private SessionInterface $session, ?callable $now = null)
    {
        $this->now = $now ?? fn () => time();
    }

    public function isLocked(): bool
    {
        $attempts = (int) $this->session->get(self::SESSION_ATTEMPTS, 0);
        $last     = (int) $this->session->get(self::SESSION_TIME, 0);
        if ($this->session->get(self::SESSION_TIME) !== null
            && ($this->now)() - $last > self::LOCK_TIMEOUT) {
            $this->clear();

            return false;
        }

        return $attempts >= self::MAX_ATTEMPTS;
    }

    public function hit(): ?string
    {
        $attempts = (int) $this->session->get(self::SESSION_ATTEMPTS, 0) + 1;

        $this->session->set(self::SESSION_ATTEMPTS, $attempts);
        $this->session->set(self::SESSION_TIME, ($this->now)());

        return $attempts >= self::MAX_ATTEMPTS ? self::ERROR_LOCKED : null;
    }

    public function clear(): void
    {
        $this->session->remove(self::SESSION_ATTEMPTS);
        $this->session->remove(self::SESSION_TIME);
    }
}
