<?php

declare(strict_types=1);

namespace App\Application\Security;

interface CsrfInterface
{
    public function token(): string;

    public function verify(string $token): bool;
}
