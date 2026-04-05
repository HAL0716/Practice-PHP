<?php

declare(strict_types=1);

namespace Tests\Core\Http;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use App\Infrastructure\Http\Session;
use App\Domain\User\User;

#[CoversClass(Session::class)]
final class SessionTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    public function testSetAndGet(): void
    {
        $session = new Session();

        $session->set('key', 'value');

        $this->assertSame('value', $session->get('key'));
    }

    public function testGetReturnsDefault(): void
    {
        $session = new Session();

        $this->assertSame('default', $session->get('key', 'default'));
    }

    public function testRemove(): void
    {
        $session = new Session();

        $session->set('key', 'value');
        $session->remove('key');

        $this->assertNull($session->get('key'));
    }

    public function testClear(): void
    {
        $session = new Session();

        $session->set('a', 1);
        $session->set('b', 2);

        $session->clear();

        $this->assertEmpty($_SESSION);
    }

    public function testLoginAndIsLoggedIn(): void
    {
        $session = new Session();

        $user = new User(1, 'name', 'test@example.com', 'hash');

        $session->login($user);

        $this->assertTrue($session->isLoggedIn());
        $this->assertSame(1, $session->userId());
    }

    public function testLogout(): void
    {
        $session = new Session();

        $user = new User(1, 'name', 'test@example.com', 'hash');

        $session->login($user);
        $session->logout();

        $this->assertFalse($session->isLoggedIn());
    }

    public function testFlash(): void
    {
        $session = new Session();

        $session->flash('key', 'value');

        $this->assertSame('value', $session->getFlash('key'));
        $this->assertNull($session->getFlash('key'));
    }

    public function testFlashError(): void
    {
        $session = new Session();

        $session->flashError('error message');

        $this->assertSame('error message', $session->error());
        $this->assertNull($session->error());
    }

    public function testFlashOld(): void
    {
        $session = new Session();

        $data = ['name' => 'haruki'];

        $session->flashOld($data);

        $this->assertSame($data, $session->old());
        $this->assertSame([], $session->old());
    }
}
