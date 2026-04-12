<?php

declare(strict_types=1);

namespace Tests\Fake\Domain;

use App\Domain\Post\Post;
use App\Domain\Post\PostRepositoryInterface;

final class FakePostRepository implements PostRepositoryInterface
{
    public ?Post $createResult = null;
    public array $findAllResult = [];

    public bool $deleteResult = true;

    public function create(int $userId, string $comment): ?Post
    {
        return $this->createResult;
    }

    public function findById(int $id): ?Post
    {
        return null;
    }

    public function findAll(): array
    {
        return $this->findAllResult;
    }

    public function delete(int $postId, int $userId): bool
    {
        return $this->deleteResult;
    }
}
