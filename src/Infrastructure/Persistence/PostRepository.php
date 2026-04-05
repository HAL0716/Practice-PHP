<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Post\Post;
use App\Domain\Post\PostRepositoryInterface;
use App\Infrastructure\Database\Schema\PostsTable;
use App\Infrastructure\Database\Schema\UsersTable;

final class PostRepository extends Repository implements PostRepositoryInterface
{
    protected function hydrate(array $row): Post
    {
        $userId = $row[PostsTable::USER_ID];
        $username = $row[UsersTable::ALIAS . '_' . UsersTable::USERNAME];

        return new Post(
            (int) $row[PostsTable::ID],
            $userId !== null ? (int) $userId : null,
            (string) $row[PostsTable::COMMENT],
            (string) $row[PostsTable::CREATED_AT],
            $username !== null ? (string) $username : null
        );
    }

    protected function baseSelect(): string
    {
        return sprintf(
            "SELECT
                %s.%s,
                %s.%s,
                %s.%s,
                %s.%s,
                %s.%s AS %s_%s
             FROM %s %s
             LEFT JOIN %s %s
               ON %s.%s = %s.%s",
            PostsTable::ALIAS,
            PostsTable::ID,
            PostsTable::ALIAS,
            PostsTable::USER_ID,
            PostsTable::ALIAS,
            PostsTable::COMMENT,
            PostsTable::ALIAS,
            PostsTable::CREATED_AT,
            UsersTable::ALIAS,
            UsersTable::USERNAME,
            UsersTable::ALIAS,
            UsersTable::USERNAME,
            PostsTable::TABLE,
            PostsTable::ALIAS,
            UsersTable::TABLE,
            UsersTable::ALIAS,
            PostsTable::ALIAS,
            PostsTable::USER_ID,
            UsersTable::ALIAS,
            UsersTable::ID
        );
    }

    public function create(int $userId, string $comment): ?Post
    {
        $sql = sprintf(
            "INSERT INTO %s (%s, %s) VALUES (?, ?)",
            PostsTable::TABLE,
            PostsTable::USER_ID,
            PostsTable::COMMENT
        );

        $params = [
            $userId,
            $comment
        ];

        if (!$this->execute($sql, $params)) {
            return null;
        }

        return $this->findById((int) $this->db->lastInsertId());
    }

    public function findById(int $id): ?Post
    {
        return $this->findOneBy(PostsTable::ALIAS . '.' . PostsTable::ID, [$id]);
    }

    public function findAll(): array
    {
        return $this->findAllOrdered(PostsTable::ALIAS . '.' . PostsTable::CREATED_AT, 'DESC');
    }

    public function delete(int $postId, int $userId): bool
    {
        $sql = sprintf(
            "DELETE FROM %s WHERE %s = ? AND %s = ?",
            PostsTable::TABLE,
            PostsTable::ID,
            PostsTable::USER_ID
        );

        return $this->execute($sql, [$postId, $userId]);
    }
}
