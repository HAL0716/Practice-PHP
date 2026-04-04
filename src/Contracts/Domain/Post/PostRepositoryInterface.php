<?php

declare(strict_types=1);

namespace App\Contracts\Domain\Post;

use App\Domain\Post\Post;

interface PostRepositoryInterface
{
    public function create(int $userId, string $comment): ?Post;

    public function findAll(): array;

    public function delete(int $postId, int $userId): bool;
}
