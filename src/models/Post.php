<?php

declare(strict_types=1);

require_once __DIR__ . '/../database/Database.php';
require_once __DIR__ . '/../database/schema/UsersTable.php';
require_once __DIR__ . '/../database/schema/PostsTable.php';

final class Post
{
    public const FIELD_ID       = 'id';
    public const FIELD_USER_ID  = 'user_id';
    public const FIELD_USERNAME = 'username';
    public const FIELD_COMMENT  = 'comment';

    private function __construct()
    {
    }

    private static function db(): PDO
    {
        return Database::connect();
    }

    public static function create(
        int $userId,
        string $comment
    ): ?array {
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
        } catch (PDOException) {
            return null;
        }

        return self::findById((int)self::db()->lastInsertId());
    }

    public static function findById(int $id): ?array
    {
        $sql = sprintf(
            "SELECT
                %s.%s AS %s,
                %s.%s AS %s,
                %s.%s AS %s,
                %s.%s AS %s
             FROM %s %s
             LEFT JOIN %s %s ON %s.%s = %s.%s
             WHERE %s.%s = ?",
            PostsTable::ALIAS,
            PostsTable::ID,
            self::FIELD_ID,
            PostsTable::ALIAS,
            PostsTable::USER_ID,
            self::FIELD_USER_ID,
            UsersTable::ALIAS,
            UsersTable::USERNAME,
            self::FIELD_USERNAME,
            PostsTable::ALIAS,
            PostsTable::COMMENT,
            self::FIELD_COMMENT,
            PostsTable::TABLE,
            PostsTable::ALIAS,
            UsersTable::TABLE,
            UsersTable::ALIAS,
            PostsTable::ALIAS,
            PostsTable::USER_ID,
            UsersTable::ALIAS,
            UsersTable::ID,
            PostsTable::ALIAS,
            PostsTable::ID
        );

        $stmt = self::db()->prepare($sql);
        $stmt->execute([$id]);

        $post = $stmt->fetch(PDO::FETCH_ASSOC);

        return $post ?: null;
    }

    public static function findAll(): array
    {
        $sql = sprintf(
            "SELECT
                %s.%s AS %s,
                %s.%s AS %s,
                %s.%s AS %s,
                %s.%s AS %s
             FROM %s %s
             LEFT JOIN %s %s ON %s.%s = %s.%s
             ORDER BY %s.%s",
            PostsTable::ALIAS,
            PostsTable::ID,
            self::FIELD_ID,
            UsersTable::ALIAS,
            UsersTable::USERNAME,
            self::FIELD_USERNAME,
            PostsTable::ALIAS,
            PostsTable::USER_ID,
            self::FIELD_USER_ID,
            PostsTable::ALIAS,
            PostsTable::COMMENT,
            self::FIELD_COMMENT,
            PostsTable::TABLE,
            PostsTable::ALIAS,
            UsersTable::TABLE,
            UsersTable::ALIAS,
            PostsTable::ALIAS,
            PostsTable::USER_ID,
            UsersTable::ALIAS,
            UsersTable::ID,
            PostsTable::ALIAS,
            PostsTable::ID
        );

        $stmt = self::db()->prepare($sql);

        try {
            $stmt->execute();
        } catch (PDOException) {
            return [];
        }

        return $stmt->fetchAll();
    }
}
