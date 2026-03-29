<?php

declare(strict_types=1);

namespace App\Core\Security;

use App\Contracts\Security\CsrfInterface;
use App\Contracts\Http\SessionInterface;

final class Csrf implements CsrfInterface
{
    private const TOKEN_LENGTH  = 32;
    private const TOKEN_MINUTE  = 60;
    private const TOKEN_TIMEOUT = self::TOKEN_MINUTE * 60;

    private const SESSION_TOKEN = 'csrf_token';
    private const SESSION_TIME  = 'csrf_token_time';

    public function __construct(private SessionInterface $session)
    {
    }

    public function token(): string
    {
        if ($this->isExpired()) {
            $this->clear();
        }

        $token = $this->session->get(self::SESSION_TOKEN);

        if (!$token) {
            $token = $this->generateToken();

            $this->session->set(self::SESSION_TOKEN, $token);
            $this->session->set(self::SESSION_TIME, time());
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

    private function generateToken(): string
    {
        return bin2hex(random_bytes(self::TOKEN_LENGTH));
    }

    private function isExpired(): bool
    {
        $tokenTime = $this->session->get(self::SESSION_TIME);

        if (!$tokenTime) {
            return true;
        }

        return (time() - $tokenTime) > self::TOKEN_TIMEOUT;
    }

    private function clear(): void
    {
        $this->session->remove(self::SESSION_TOKEN);
        $this->session->remove(self::SESSION_TIME);
    }
}
