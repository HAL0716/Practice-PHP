<?php

declare(strict_types=1);

namespace App\Application\Security;

interface LoginThrottleInterface
{
    public function isLocked(): bool;

    public function hit(): ?string;

    public function clear(): void;
}
