<?php

declare(strict_types=1);

namespace Tests\Core;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use App\Core\Repository;
use Tests\Fake\Database\FakeDatabase;

#[CoversClass(Repository::class)]
final class RepositoryTest extends TestCase
{
    public function testFindOne(): void
    {
        $repo = $this->newRepository([
            ['id' => 1, 'name' => 'test']
        ]);

        $result = $repo->findOne('id', [1]);

        $this->assertSame(1, $result->id);
        $this->assertSame('test', $result->name);
    }

    public function testFindOneReturnsNull(): void
    {
        $repo = $this->newRepository();

        $result = $repo->findOne('id', [1]);

        $this->assertNull($result);
    }

    public function testFindAll(): void
    {
        $repo = $this->newRepository([
            ['id' => 1],
            ['id' => 2],
        ]);

        $results = $repo->findAll('id');

        $this->assertCount(2, $results);
        $this->assertSame(1, $results[0]->id);
        $this->assertSame(2, $results[1]->id);
    }

    public function testInvalidOrderDirection(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $repo = $this->newRepository();

        $repo->findAll('id', 'invalid');
    }

    private function newRepository(array $data = []): TestRepository
    {
        $db = new FakeDatabase();
        $db->rows = $data;

        return new TestRepository($db);
    }
}
