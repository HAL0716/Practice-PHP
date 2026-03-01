<?php

declare(strict_types=1);

require_once __DIR__ . '/../core/Repository.php';
require_once __DIR__ . '/../database/schema/UsersTable.php';
require_once __DIR__ . '/../database/schema/PostsTable.php';
require_once __DIR__ . '/../entities/PostEntity.php';

final class PostRepository extends Repository
{
    protected static function hydrate(array $row): PostEntity
    {
        return new PostEntity(
            (int)$row[PostsTable::ID],
            (int)$row[PostsTable::USER_ID],
            (string)$row[PostsTable::COMMENT],
            $row[UsersTable::ALIAS . '_' . UsersTable::USERNAME]
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
            PostsTable::ALIAS,
            PostsTable::ID,
            PostsTable::ALIAS,
            PostsTable::USER_ID,
            PostsTable::ALIAS,
            PostsTable::COMMENT,
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
        } catch (PDOException) {
            return null;
        }

        return self::findById((int)$db->lastInsertId());
    }

    public static function findById(int $id): ?PostEntity
    {
        return self::findOneBy(PostsTable::ALIAS . '.' . PostsTable::ID, [$id]);
    }

    public static function findAll(): array {
        return self::findAllOrdered(PostsTable::ALIAS . '.' . PostsTable::CREATED_AT, 'ASC');
    }
}
