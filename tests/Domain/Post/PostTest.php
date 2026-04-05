<?php

declare(strict_types=1);

namespace Tests\Domain\Post;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use App\Domain\Post\Post;

#[CoversClass(Post::class)]
final class PostTest extends TestCase
{
    private int $id = 1;
    private ?int $userId = 1;
    private string $comment = 'comment';
    private string $createdAt = '2024-01-01 00:00:00';
    private ?string $username = 'name';

    private function createPost(): Post
    {
        return new Post(
            id: $this->id,
            userId: $this->userId,
            comment: $this->comment,
            createdAt: $this->createdAt,
            username: $this->username
        );
    }

    public function testId(): void
    {
        $post = $this->createPost();

        $this->assertSame(1, $post->id());
    }

    public function testUserId(): void
    {
        $post = $this->createPost();

        $this->assertSame(1, $post->userId());
    }

    public function testComment(): void
    {
        $post = $this->createPost();

        $this->assertSame('comment', $post->comment());
    }

    public function testUsername(): void
    {
        $post = $this->createPost();

        $this->assertSame('name', $post->username());
    }

    public function testCreatedAtJst(): void
    {
        $post = $this->createPost();

        $this->assertSame('2024-01-01 09:00', $post->createdAtJst());
    }
}
