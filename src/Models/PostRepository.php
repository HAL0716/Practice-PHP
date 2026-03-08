<?php

declare(strict_types=1);

namespace App\Models;

final class PostRepository extends \App\Core\Repository
{
    protected static function hydrate(array $row): \App\Entities\PostEntity
    {
        $userId = $row[\App\Database\Schema\PostsTable::USER_ID];
        $username = $row[\App\Database\Schema\UsersTable::ALIAS . '_' . \App\Database\Schema\UsersTable::USERNAME];

        return new \App\Entities\PostEntity(
            (int) $row[\App\Database\Schema\PostsTable::ID],
            $userId !== null ? (int) $userId : null,
            (string) $row[\App\Database\Schema\PostsTable::COMMENT],
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
                %s.%s AS %s_%s
             FROM %s %s
             LEFT JOIN %s %s
               ON %s.%s = %s.%s",
            \App\Database\Schema\PostsTable::ALIAS,
            \App\Database\Schema\PostsTable::ID,
            \App\Database\Schema\PostsTable::ALIAS,
            \App\Database\Schema\PostsTable::USER_ID,
            \App\Database\Schema\PostsTable::ALIAS,
            \App\Database\Schema\PostsTable::COMMENT,
            \App\Database\Schema\UsersTable::ALIAS,
            \App\Database\Schema\UsersTable::USERNAME,
            \App\Database\Schema\UsersTable::ALIAS,
            \App\Database\Schema\UsersTable::USERNAME,
            \App\Database\Schema\PostsTable::TABLE,
            \App\Database\Schema\PostsTable::ALIAS,
            \App\Database\Schema\UsersTable::TABLE,
            \App\Database\Schema\UsersTable::ALIAS,
            \App\Database\Schema\PostsTable::ALIAS,
            \App\Database\Schema\PostsTable::USER_ID,
            \App\Database\Schema\UsersTable::ALIAS,
            \App\Database\Schema\UsersTable::ID
        );
    }

    public static function create(
        int $userId,
        string $comment
    ): ?\App\Entities\PostEntity {

        $db = self::db();

        $sql = sprintf(
            "INSERT INTO %s (%s, %s)
             VALUES (?, ?)",
            \App\Database\Schema\PostsTable::TABLE,
            \App\Database\Schema\PostsTable::USER_ID,
            \App\Database\Schema\PostsTable::COMMENT
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

    public static function findById(int $id): ?\App\Entities\PostEntity
    {
        return self::findOneBy(\App\Database\Schema\PostsTable::ALIAS . '.' . \App\Database\Schema\PostsTable::ID, [$id]);
    }

    public static function findAll(): array
    {
        return self::findAllOrdered(\App\Database\Schema\PostsTable::ALIAS . '.' . \App\Database\Schema\PostsTable::CREATED_AT, 'ASC');
    }
}
