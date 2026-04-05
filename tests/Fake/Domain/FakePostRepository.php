<?php

declare(strict_types=1);

namespace Tests\Fake\Domain;

use App\Contracts\Domain\Post\PostRepositoryInterface;
use App\Domain\Post\Post;

final class FakePostRepository implements PostRepositoryInterface
{
    public ?Post $createResult = null;
    public array $findAllResult = [];

    public bool $deleteResult = true;

    public function create(int $userId, string $comment): ?Post
    {
        return $this->createResult;
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
