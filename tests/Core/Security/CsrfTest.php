<?php

declare(strict_types=1);

namespace Tests\Core\Security;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use App\Contracts\Security\CsrfInterface;
use App\Core\Security\Csrf;
use Tests\Fake\Http\FakeSession;

#[CoversClass(Csrf::class)]
final class CsrfTest extends TestCase
{
    private FakeSession $session;
    private Csrf $csrf;

    protected function setUp(): void
    {
        $this->session = new FakeSession();

        $this->csrf = new Csrf($this->session);
    }

    public function testImplementsInterface(): void
    {
        $this->assertInstanceOf(CsrfInterface::class, $this->csrf);
    }

    public function testTokenGeneratesAndStores(): void
    {
        $token = $this->csrf->token();

        $this->assertNotEmpty($token);
        $this->assertSame($token, $this->session->get('csrf_token'));
    }

    public function testTokenReturnsSameIfNotExpired(): void
    {
        $token1 = $this->csrf->token();
        $token2 = $this->csrf->token();

        $this->assertSame($token1, $token2);
    }

    public function testVerifySuccess(): void
    {
        $token = $this->csrf->token();

        $this->assertTrue($this->csrf->verify($token));

        $this->assertNull($this->session->get('csrf_token'));
    }

    public function testVerifyFailWithInvalidToken(): void
    {
        $this->csrf->token();

        $this->assertFalse($this->csrf->verify('invalid'));
    }

    public function testVerifyFailWhenNoToken(): void
    {
        $this->assertFalse($this->csrf->verify('anything'));
    }

    public function testExpiredToken(): void
    {
        $this->session->set('csrf_token', 'token');
        $this->session->set('csrf_token_time', time() - 999999);

        $this->assertFalse($this->csrf->verify('token'));
    }
}
