<?php

declare(strict_types=1);

namespace App\Core\Security;

use App\Contracts\Http\SessionInterface;
use App\Contracts\Security\CsrfInterface;

final class Csrf implements CsrfInterface
{
    private const TOKEN_LENGTH  = 32;
    private const TOKEN_MINUTE  = 60;
    private const TOKEN_TIMEOUT = self::TOKEN_MINUTE * 60;

    private const SESSION_TOKEN = 'csrf_token';
    private const SESSION_TIME  = 'csrf_token_time';

    private $now;
    private $random;

    public function __construct(private SessionInterface $session, ?callable $now = null, ?callable $random = null)
    {
        $this->now = $now ?? fn () => time();
        $this->random = $random ?? fn (int $length) => random_bytes($length);
    }

    public function token(): string
    {
        if ($this->isExpired()) {
            $this->clear();
        }

        $token = $this->session->get(self::SESSION_TOKEN);

        if (!$token) {
            $token = bin2hex(($this->random)(self::TOKEN_LENGTH));

            $this->session->set(self::SESSION_TOKEN, $token);
            $this->session->set(self::SESSION_TIME, ($this->now)());
        }

        return $token;
    }

    public function verify(string $token): bool
    {
        if ($this->isExpired()) {
            $this->clear();
            return false;
        }

        $sessionToken = $this->session->get(self::SESSION_TOKEN);

        if (!$sessionToken) {
            return false;
        }

        $valid = hash_equals($sessionToken, $token);

        if ($valid) {
            $this->clear();
        }

        return $valid;
    }

    private function isExpired(): bool
    {
        $tokenTime = $this->session->get(self::SESSION_TIME);

        if (!$tokenTime) {
            return true;
        }

        return (($this->now)() - $tokenTime) > self::TOKEN_TIMEOUT;
    }

    private function clear(): void
    {
        $this->session->remove(self::SESSION_TOKEN);
        $this->session->remove(self::SESSION_TIME);
    }
}
