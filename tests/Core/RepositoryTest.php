<?php

declare(strict_types=1);

namespace Tests\Core;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use App\Infrastructure\Persistence\Repository;
use Tests\Fake\Database\FakeDatabase;

#[CoversClass(Repository::class)]
final class RepositoryTest extends TestCase
{
    public function testFindOneReturnsObject(): void
    {
        $repo = $this->createRepository([
            ['id' => 1, 'name' => 'test']
        ]);

        $result = $repo->findOne('id', [1]);

        $this->assertNotNull($result);
        $this->assertSame(1, $result->id);
        $this->assertSame('test', $result->name);
    }

    public function testFindOneReturnsNull(): void
    {
        $repo = $this->createRepository([
            ['id' => 2, 'name' => 'test']
        ]);

        $result = $repo->findOne('id', [1]);

        $this->assertNull($result);
    }

    public function testFindAllReturnsAllRows(): void
    {
        $repo = $this->createRepository([
            ['id' => 1],
            ['id' => 2],
        ]);

        $results = $repo->findAll('id');

        $this->assertCount(2, $results);
        $this->assertSame(1, $results[0]->id);
        $this->assertSame(2, $results[1]->id);
    }

    public function testFindAllWithDescendingOrder(): void
    {
        $repo = $this->createRepository([
            ['id' => 1],
            ['id' => 2],
        ]);

        $results = $repo->findAll('id', 'DESC');

        $this->assertSame(2, $results[0]->id);
        $this->assertSame(1, $results[1]->id);
    }

    public function testInvalidOrderDirectionThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $repo = $this->createRepository();

        $repo->findAll('id', 'INVALID');
    }

    public function testFetchAllHydratesObjects(): void
    {
        $repo = $this->createRepository([
            ['id' => 1, 'name' => 'abc']
        ]);

        $results = $repo->findAll('id');

        $this->assertInstanceOf(\stdClass::class, $results[0]);
        $this->assertSame('abc', $results[0]->name);
    }

    private function createRepository(array $rows = []): TestRepository
    {
        $db = new FakeDatabase();
        $db->rows = $rows;

        return new TestRepository($db);
    }
}
