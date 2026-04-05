<?php

declare(strict_types=1);

namespace Tests\Domain\User;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use App\Domain\User\User;
use App\Infrastructure\Persistence\UserRepository;
use Tests\Fake\Infrastructure\Database\FakeDatabase;

#[CoversClass(UserRepository::class)]
final class UserRepositoryTest extends TestCase
{
    public function testCreateReturnsUser(): void
    {
        $repo = $this->createRepository([
            [
                'id' => 1,
                'username' => 'name',
                'email' => 'test@example.com',
                'password' => password_hash('pass1234', PASSWORD_DEFAULT)
            ]
        ], lastId: 1);

        $user = $repo->create('name', 'test@example.com', 'pass1234');

        $this->assertInstanceOf(User::class, $user);
        $this->assertSame(1, $user->id());
        $this->assertSame('name', $user->username());
        $this->assertSame('test@example.com', $user->email());

        $this->assertTrue($user->verifyPassword('pass1234'));
    }

    public function testCreateFailsReturnsNull(): void
    {
        $repo = $this->createRepository([], shouldFail: true);

        $user = $repo->create('name', 'test@example.com', 'pass1234');

        $this->assertNull($user);
    }

    public function testFindByIdReturnsUser(): void
    {
        $repo = $this->createRepository([
            [
                'id' => 1,
                'username' => 'name',
                'email' => 'test@example.com',
                'password' => password_hash('pass1234', PASSWORD_DEFAULT)
            ]
        ]);

        $user = $repo->findById(1);

        $this->assertInstanceOf(User::class, $user);
        $this->assertSame(1, $user->id());
    }

    public function testFindByIdReturnsNull(): void
    {
        $repo = $this->createRepository([]);

        $user = $repo->findById(999);

        $this->assertNull($user);
    }

    public function testFindByEmailReturnsUser(): void
    {
        $repo = $this->createRepository([
            [
                'id' => 1,
                'username' => 'name',
                'email' => 'test@example.com',
                'password' => password_hash('pass1234', PASSWORD_DEFAULT)
            ]
        ]);

        $user = $repo->findByEmail('test@example.com');

        $this->assertInstanceOf(User::class, $user);
        $this->assertSame('test@example.com', $user->email());
    }

    public function testFindByEmailReturnsNull(): void
    {
        $repo = $this->createRepository([]);

        $user = $repo->findByEmail('none@example.com');

        $this->assertNull($user);
    }

    public function testUpdateReturnsFalse(): void
    {
        $repo = $this->createRepository([], shouldFail: true);

        $this->assertFalse($repo->update(1, name: 'new'));
        $this->assertFalse($repo->update(1, email: 'new@example.com'));
        $this->assertFalse($repo->update(1, password: 'newpass1234'));
    }

    public function testUpdateName(): void
    {
        $repo = $this->createRepository();

        $result = $repo->update(1, name: 'new');

        $this->assertTrue($result);
    }

    public function testUpdateEmail(): void
    {
        $repo = $this->createRepository();

        $result = $repo->update(1, email: 'new@example.com');

        $this->assertTrue($result);
    }

    public function testUpdatePassword(): void
    {
        $repo = $this->createRepository();

        $result = $repo->update(1, password: 'newpass1234');

        $this->assertTrue($result);
    }

    public function testDeleteReturnsTrue(): void
    {
        $repo = $this->createRepository();

        $this->assertTrue($repo->delete(1));
    }

    private function createRepository(array $rows = [], ?int $lastId = null, bool $shouldFail = false): UserRepository
    {
        $db = new FakeDatabase();
        $db->rows = $rows;

        if ($lastId !== null) {
            $db->lastId = $lastId;
        }

        $db->shouldFail = $shouldFail;

        return new UserRepository($db);
    }
}
