<?php

declare(strict_types=1);

require_once __DIR__ . '/../database/Database.php';
require_once __DIR__ . '/../database/schema/UsersTable.php';
require_once __DIR__ . '/../database/schema/PostsTable.php';
require_once __DIR__ . '/../entities/PostEntity.php';

final class PostRepository
{
    private function __construct()
    {
    }

    private static function db(): PDO
    {
        return Database::connect();
    }

    private static function baseSelect(): string
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

    private static function hydrate(array $row): PostEntity
    {
        return new PostEntity(
            (int)$row[PostsTable::ID],
            (int)$row[PostsTable::USER_ID],
            (string)$row[PostsTable::COMMENT],
            $row[UsersTable::ALIAS . '_' . UsersTable::USERNAME]
        );
    }

    private static function fetchOne(PDOStatement $stmt): ?PostEntity
    {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? self::hydrate($row) : null;
    }

    private static function fetchAll(PDOStatement $stmt): array
    {
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(
            fn (array $row) => self::hydrate($row),
            $rows
        );
    }

    public static function create(int $userId, string $comment): ?PostEntity
    {
        $sql = sprintf(
            "INSERT INTO %s (%s, %s) VALUES (?, ?)",
            PostsTable::TABLE,
            PostsTable::USER_ID,
            PostsTable::COMMENT
        );

        $stmt = self::db()->prepare($sql);

        try {
            $stmt->execute([
                $userId,
                $comment
            ]);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return null;
        }

        return self::findById((int)self::db()->lastInsertId());
    }

    public static function findById(int $id): ?PostEntity
    {
        $sql = self::baseSelect() .
            sprintf(
                " WHERE %s.%s = ?",
                PostsTable::ALIAS,
                PostsTable::ID
            );

        $stmt = self::db()->prepare($sql);
        $stmt->execute([$id]);

        return self::fetchOne($stmt);
    }

    public static function findAll(): array
    {
        $sql = self::baseSelect() .
            sprintf(
                " ORDER BY %s.%s",
                PostsTable::ALIAS,
                PostsTable::ID
            );

        $stmt = self::db()->prepare($sql);
        $stmt->execute();

        return self::fetchAll($stmt);
    }
}
