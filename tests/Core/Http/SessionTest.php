<?php

declare(strict_types=1);

namespace Tests\Core\Http;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use App\Contracts\Http\SessionInterface;
use App\Core\Http\Session;
use App\Domain\User\User;

#[CoversClass(Session::class)]
final class SessionTest extends TestCase
{
    private Session $session;

    protected function setUp(): void
    {
        $_SESSION = [];

        $this->session = new Session();
    }

    public function testImplementsInterface(): void
    {
        $this->assertInstanceOf(SessionInterface::class, $this->session);
    }

    public function testSetAndGet(): void
    {
        $this->session->set('key', 'value');

        $this->assertSame('value', $this->session->get('key'));
    }

    public function testGetReturnsDefaultWhenKeyNotFound(): void
    {
        $this->assertSame('default', $this->session->get('none', 'default'));
    }

    public function testRemove(): void
    {
        $this->session->set('key', 'value');

        $this->session->remove('key');

        $this->assertNull($this->session->get('key'));
    }

    public function testClear(): void
    {
        $this->session->set('a', 1);

        $this->session->clear();

        $this->assertSame([], $_SESSION);
    }

    public function testLoginSetsUserState(): void
    {
        $user = new User(10, 'name', 'email@example.com', 'password');

        $this->session->login($user);

        $this->assertTrue($this->session->isLoggedIn());
        $this->assertSame(10, $this->session->userId());
    }

    public function testIsLoggedInReturnsFalseWhenNotLoggedIn(): void
    {
        $this->assertFalse($this->session->isLoggedIn());
    }

    public function testLogoutClearsSession(): void
    {
        $_SESSION['test'] = 1;

        $this->session->logout();

        $this->assertSame([], $_SESSION);
    }

    public function testFlashStoresAndRemovesValue(): void
    {
        $this->session->flash('key', 'value');

        $this->assertSame('value', $this->session->getFlash('key'));
        $this->assertNull($this->session->getFlash('key'));
    }

    public function testFlashError(): void
    {
        $this->session->flashError('error');

        $this->assertSame('error', $this->session->error());
        $this->assertNull($this->session->error());
    }

    public function testFlashOld(): void
    {
        $data = ['a' => 1];

        $this->session->flashOld($data);

        $this->assertSame($data, $this->session->old());
        $this->assertSame([], $this->session->old());
    }
}
