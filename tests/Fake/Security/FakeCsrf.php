<?php

declare(strict_types=1);

namespace Tests\Fake\Security;

use App\Contracts\Security\CsrfInterface;

final class FakeCsrf implements CsrfInterface
{
    public function __construct(private bool $verifyResult = true, private string $token = 'token')
    {
    }

    public function token(): string
    {
        return $this->token;
    }

    public function verify(string $token): bool
    {
        return $this->verifyResult;
    }
}
