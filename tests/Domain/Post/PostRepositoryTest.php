<?php

declare(strict_types=1);

namespace Tests\Domain\Post;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use App\Domain\Post\Post;
use App\Infrastructure\Persistence\PostRepository;
use Tests\Fake\Database\FakeDatabase;

#[CoversClass(PostRepository::class)]
final class PostRepositoryTest extends TestCase
{
    public function testCreateReturnsPost(): void
    {
        $repo = $this->createRepository([
            [
                'id' => 1,
                'user_id' => 1,
                'comment' => 'comment',
                'created_at' => '2024-01-01 00:00:00',
                'u_username' => 'name'
            ]
        ], lastId: 1);

        $post = $repo->create(1, 'comment');

        $this->assertInstanceOf(Post::class, $post);
        $this->assertSame(1, $post->id());
        $this->assertSame('comment', $post->comment());
        $this->assertSame('name', $post->username());
    }

    public function testCreateFailsReturnsNull(): void
    {
        $repo = $this->createRepository([], shouldFail: true);

        $post = $repo->create(1, 'comment');

        $this->assertNull($post);
    }

    public function testFindByIdReturnsPost(): void
    {
        $repo = $this->createRepository([
            [
                'id' => 1,
                'user_id' => 1,
                'comment' => 'comment',
                'created_at' => '2024-01-01 00:00:00',
                'u_username' => 'name'
            ]
        ]);

        $post = $repo->findById(1);

        $this->assertInstanceOf(Post::class, $post);
        $this->assertSame(1, $post->id());
        $this->assertSame('comment', $post->comment());
        $this->assertSame('name', $post->username());
    }

    public function testFindByIdReturnsNull(): void
    {
        $repo = $this->createRepository([]);

        $post = $repo->findById(999);

        $this->assertNull($post);
    }

    public function testFindAllReturnsOrderedPosts(): void
    {
        $repo = $this->createRepository([
            [
                'id' => 1,
                'user_id' => 1,
                'comment' => 'a',
                'created_at' => '2024-01-01 00:00:00',
                'u_username' => 'name'
            ],
            [
                'id' => 2,
                'user_id' => 1,
                'comment' => 'b',
                'created_at' => '2024-01-02 00:00:00',
                'u_username' => 'name'
            ]
        ]);

        $posts = $repo->findAll();

        $this->assertCount(2, $posts);
        $this->assertSame(2, $posts[0]->id());
        $this->assertSame(1, $posts[1]->id());
    }

    public function testDeleteReturnsTrue(): void
    {
        $repo = $this->createRepository([]);

        $result = $repo->delete(1, 1);

        $this->assertTrue($result);
    }

    private function createRepository(array $rows = [], ?int $lastId = null, bool $shouldFail = false): PostRepository
    {
        $db = new FakeDatabase();
        $db->rows = $rows;

        if ($lastId !== null) {
            $db->lastId = $lastId;
        }

        $db->shouldFail = $shouldFail;

        return new PostRepository($db);
    }
}
