<?php

declare(strict_types=1);

namespace Tests\Fake\Infrastructure\Security;

use App\Application\Security\CsrfInterface;

final class FakeCsrf implements CsrfInterface
{
    public function __construct(private string $token = 'token')
    {
    }

    public function token(): string
    {
        return $this->token;
    }

    public function verify(string $token): bool
    {
        return $token === $this->token;
    }
}
