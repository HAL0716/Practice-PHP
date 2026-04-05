<?php

declare(strict_types=1);

namespace App\Domain\Post;

interface PostRepositoryInterface
{
    public function create(int $userId, string $comment): ?Post;

    public function findAll(): array;

    public function delete(int $postId, int $userId): bool;
}
