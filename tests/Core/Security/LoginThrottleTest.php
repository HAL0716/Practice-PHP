<?php

declare(strict_types=1);

namespace Tests\Core\Security;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use App\Infrastructure\Security\LoginThrottle;
use Tests\Fake\Http\FakeSession;

#[CoversClass(LoginThrottle::class)]
final class LoginThrottleTest extends TestCase
{
    public function testNotLockedInitially(): void
    {
        $throttle = new LoginThrottle(new FakeSession());

        $this->assertFalse($throttle->isLocked());
    }

    public function testLocksAfterMaxAttempts(): void
    {
        $session = new FakeSession();

        $throttle = new LoginThrottle($session, now: fn () => 1000);

        for ($i = 0; $i < 4; $i++) {
            $this->assertNull($throttle->hit());
        }

        $this->assertSame(LoginThrottle::ERROR_LOCKED, $throttle->hit());
        $this->assertTrue($throttle->isLocked());
    }

    public function testHitIncrementsAttempts(): void
    {
        $session = new FakeSession();

        $throttle = new LoginThrottle($session, now: fn () => 1000);

        $throttle->hit();
        $throttle->hit();

        $this->assertSame(2, $session->get('login_attempts'));
    }

    public function testUnlocksAfterTimeout(): void
    {
        $session = new FakeSession();

        // ロック状態まで進める
        $throttle = new LoginThrottle($session, now: fn () => 1000);

        for ($i = 0; $i < 5; $i++) {
            $throttle->hit();
        }

        $this->assertTrue($throttle->isLocked());

        // 時間経過後
        $throttle = new LoginThrottle($session, now: fn () => 100000);

        $this->assertFalse($throttle->isLocked());
    }

    public function testClearResetsState(): void
    {
        $session = new FakeSession();

        $throttle = new LoginThrottle($session, now: fn () => 1000);

        $throttle->hit();
        $throttle->clear();

        $this->assertFalse($throttle->isLocked());
        $this->assertNull($session->get('login_attempts'));
    }
}
