<?php

declare(strict_types=1);

namespace Tests\Core\Security;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use App\Core\Security\Csrf;
use Tests\Fake\Http\FakeSession;

#[CoversClass(Csrf::class)]
final class CsrfTest extends TestCase
{
    public function testGeneratesToken(): void
    {
        $session = new FakeSession();

        $csrf = $this->createCsrf($session, now: 1000, random: 'a');

        $token = $csrf->token();

        $this->assertSame(bin2hex(str_repeat('a', 32)), $token);
    }

    public function testReturnsSameTokenIfNotExpired(): void
    {
        $session = new FakeSession();

        $csrf = $this->createCsrf($session, now: 1000, random: 'a');

        $token1 = $csrf->token();
        $token2 = $csrf->token();

        $this->assertSame($token1, $token2);
    }

    public function testVerifySuccess(): void
    {
        $session = new FakeSession();

        $csrf = $this->createCsrf($session, now: 1000, random: 'a');

        $token = $csrf->token();

        $this->assertTrue($csrf->verify($token));
    }

    public function testVerifyFailsWithWrongToken(): void
    {
        $session = new FakeSession();

        $csrf = $this->createCsrf($session, now: 1000, random: 'a');

        $csrf->token();

        $this->assertFalse($csrf->verify('wrong'));
    }

    public function testVerifyFailsWhenExpired(): void
    {
        $session = new FakeSession();

        $csrf = $this->createCsrf($session, now: 1000, random: 'a');

        $token = $csrf->token();

        $csrf = $this->createCsrf($session, now: 100000, random: 'a');

        $this->assertFalse($csrf->verify($token));
    }

    public function testTokenRegeneratesWhenExpired(): void
    {
        $session = new FakeSession();

        $csrf1 = $this->createCsrf($session, now: 0, random: 'a');

        $token1 = $csrf1->token();

        $csrf2 = $this->createCsrf($session, now: 100000, random: 'b');

        $token2 = $csrf2->token();

        $this->assertNotSame($token1, $token2);
    }

    private function createCsrf(FakeSession $session, int $now, string $random): Csrf
    {
        return new Csrf($session, now: fn () => $now, random: fn ($len) => str_repeat($random, $len));
    }
}
