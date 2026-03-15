<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Repository;
use App\Entities\PostEntity;
use App\Database\Schema\PostsTable;
use App\Database\Schema\UsersTable;

final class PostRepository extends Repository
{
    protected static function hydrate(array $row): PostEntity
    {
        $userId = $row[PostsTable::USER_ID];
        $username = $row[UsersTable::ALIAS . '_' . UsersTable::USERNAME];

        return new \App\Entities\PostEntity(
            (int) $row[PostsTable::ID],
            $userId !== null ? (int) $userId : null,
            (string) $row[PostsTable::COMMENT],
            (string) $row[PostsTable::CREATED_AT],
            $username !== null ? (string) $username : null
        );
    }

    protected static function baseSelect(): string
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

    public static function create(
        int $userId,
        string $comment
    ): ?PostEntity {

        $db = self::db();

        $sql = sprintf(
            "INSERT INTO %s (%s, %s)
             VALUES (?, ?)",
            PostsTable::TABLE,
            PostsTable::USER_ID,
            PostsTable::COMMENT
        );

        $params = [
            $userId,
            $comment
        ];

        try {
            self::execute($sql, $params);
        } catch (\PDOException) {
            return null;
        }

        return self::findById((int)$db->lastInsertId());
    }

    public static function findById(int $id): ?PostEntity
    {
        return self::findOneBy(PostsTable::ALIAS . '.' . PostsTable::ID, [$id]);
    }

    public static function findAll(): array
    {
        return self::findAllOrdered(PostsTable::ALIAS . '.' . PostsTable::CREATED_AT, 'ASC');
    }

    public static function delete(int $postId, int $userId): bool
    {
        $sql = sprintf(
            "DELETE FROM %s WHERE %s = ? AND %s = ?",
            PostsTable::TABLE,
            PostsTable::ID,
            PostsTable::USER_ID
        );

        try {
            self::execute($sql, [$postId, $userId]);
            return true;
        } catch (\PDOException) {
            return false;
        }
    }
}
