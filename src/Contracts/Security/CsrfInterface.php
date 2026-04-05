<?php

declare(strict_types=1);

namespace App\Contracts\Security;

interface CsrfInterface
{
    public function token(): string;

    public function verify(string $token): bool;
}
