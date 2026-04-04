<?php

declare(strict_types=1);

namespace Tests\Domain\User;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use App\Domain\User\User;

#[CoversClass(User::class)]
final class UserTest extends TestCase
{
    private int $id = 1;
    private string $username = 'name';
    private string $email = 'test@example.com';
    private string $password = 'pass1234';

    private function createUser(): User
    {
        return new User(
            id: $this->id,
            username: $this->username,
            email: $this->email,
            passwordHash: password_hash($this->password, PASSWORD_DEFAULT)
        );
    }

    public function testId(): void
    {
        $user = $this->createUser();

        $this->assertSame(1, $user->id());
    }

    public function testUsername(): void
    {
        $user = $this->createUser();

        $this->assertSame('name', $user->username());
    }

    public function testEmail(): void
    {
        $user = $this->createUser();

        $this->assertSame('test@example.com', $user->email());
    }

    public function testVerifyPasswordSuccess(): void
    {
        $user = $this->createUser();

        $this->assertTrue($user->verifyPassword('pass1234'));
    }

    public function testVerifyPasswordFail(): void
    {
        $user = $this->createUser();

        $this->assertFalse($user->verifyPassword('wrong-password'));
    }
}
