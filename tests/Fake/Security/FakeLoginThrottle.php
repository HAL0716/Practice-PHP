<?php

declare(strict_types=1);

namespace Tests\Fake\Security;

use App\Contracts\Security\LoginThrottleInterface;

final class FakeLoginThrottle implements LoginThrottleInterface
{
    public ?string $hitResult = null;
    public bool $locked = false;
    public bool $cleared = false;

    public function __construct()
    {
    }

    public function isLocked(): bool
    {
        return $this->locked;
    }

    public function hit(): ?string
    {
        return $this->hitResult;
    }

    public function clear(): void
    {
        $this->cleared = true;
    }
}
