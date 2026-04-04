<?php

declare(strict_types=1);

namespace Tests\Domain\Post;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use App\Domain\Post\Post;
use App\Domain\Post\PostRepository;
use Tests\Fake\Database\FakeDatabase;

#[CoversClass(PostRepository::class)]
final class PostRepositoryTest extends TestCase
{
    public function testCreate(): void
    {
        $db = new FakeDatabase();
        $db->lastId = 1;
        $db->rows = [
            [
                'id' => 1,
                'user_id' => 1,
                'comment' => 'comment',
                'created_at' => '2024-01-01 00:00:00',
                'u_username' => 'name'
            ]
        ];

        $repo = new PostRepository($db);

        $post = $repo->create(1, 'comment');

        $this->assertInstanceOf(Post::class, $post);
        $this->assertSame(1, $post->id());
    }

    public function testFind(): void
    {
        $repo = $this->newRepository([
            [
                'id' => 1,
                'user_id' => 1,
                'comment' => 'comment',
                'created_at' => '2024-01-01 00:00:00',
                'u_username' => 'name'
            ]
        ]);

        $post = $repo->findById(1);

        $this->assertSame(1, $post->id());
        $this->assertSame('comment', $post->comment());
        $this->assertSame('name', $post->username());
    }

    public function testDelete(): void
    {
        $repo = $this->newRepository([
            [
                'id' => 1,
                'user_id' => 1,
                'comment' => 'comment',
                'created_at' => '2024-01-01 00:00:00',
                'u_username' => 'name'
            ]
        ]);

        $this->assertTrue($repo->delete(1, 1));
    }

    private function newRepository(array $rows = []): PostRepository
    {
        $db = new FakeDatabase();
        $db->rows = $rows;

        return new PostRepository($db);
    }
}
