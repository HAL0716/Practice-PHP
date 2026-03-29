<?php

declare(strict_types=1);

namespace Tests\Core\Security;

use App\Contracts\Security\LoginThrottleInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use App\Core\Security\LoginThrottle;
use Tests\Fake\Http\FakeSession;

#[CoversClass(LoginThrottle::class)]
final class LoginThrottleTest extends TestCase
{
    private FakeSession $session;
    private LoginThrottle $throttle;

    protected function setUp(): void
    {
        $this->session = new FakeSession();

        $this->throttle = new LoginThrottle($this->session);
    }

    public function testImplementsInterface(): void
    {
        $this->assertInstanceOf(LoginThrottleInterface::class, $this->throttle);
    }

    public function testInitiallyNotLocked(): void
    {
        $this->assertFalse($this->throttle->isLocked());
    }

    public function testHitIncrementsAttempts(): void
    {
        $this->throttle->hit();

        $this->assertSame(1, $this->session->get('login_attempts'));
    }

    public function testLockAfterMaxAttempts(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $result = $this->throttle->hit();
        }

        $this->assertSame(LoginThrottle::ERROR_LOCKED, $result);
        $this->assertTrue($this->throttle->isLocked());
    }

    public function testClearResetsState(): void
    {
        $this->throttle->hit();

        $this->throttle->clear();

        $this->assertNull($this->session->get('login_attempts'));
        $this->assertFalse($this->throttle->isLocked());
    }

    public function testUnlockAfterTimeout(): void
    {
        $this->session->set('login_attempts', 5);
        $this->session->set('login_attempt_time', time() - 999999);

        $this->assertFalse($this->throttle->isLocked());
    }
}
