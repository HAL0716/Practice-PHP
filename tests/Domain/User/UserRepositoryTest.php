<?php

declare(strict_types=1);

namespace Tests\Domain\User;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use App\Domain\User\User;
use App\Domain\User\UserRepository;
use Tests\Fake\Database\FakeDatabase;

#[CoversClass(UserRepository::class)]
final class UserRepositoryTest extends TestCase
{
    public function testCreate(): void
    {
        $db = new FakeDatabase();
        $db->lastId = 1;
        $db->rows = [
            [
                'id' => 1,
                'username' => 'name',
                'email' => 'test@example.com',
                'password' => 'hash'
            ]
        ];

        $repo = new UserRepository($db);

        $user = $repo->create('name', 'test@example.com', 'pass');

        $this->assertInstanceOf(User::class, $user);
        $this->assertSame(1, $user->id());
    }

    public function testFind(): void
    {
        $repo = $this->newRepository([
            [
                'id' => 1,
                'username' => 'name',
                'email' => 'test@example.com',
                'password' => 'hash'
            ]
        ]);

        $userById = $repo->findById(1);
        $this->assertSame(1, $userById->id());
        $this->assertSame('name', $userById->username());

        $userByEmail = $repo->findByEmail('test@example.com');
        $this->assertSame('test@example.com', $userByEmail->email());
    }

    public function testUpdate(): void
    {
        $repo = $this->newRepository();

        $this->assertFalse($repo->update(1));
    }

    public function testDelete(): void
    {
        $repo = $this->newRepository([
            [
                'id' => 1,
                'username' => 'name',
                'email' => 'test@example.com',
                'password' => 'hash'
            ]
        ]);

        $this->assertTrue($repo->delete(1));
    }

    private function newRepository(array $data = []): UserRepository
    {
        $db = new FakeDatabase();
        $db->rows = $data;

        return new UserRepository($db);
    }
}
