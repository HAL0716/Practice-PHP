<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/Database.php';

final class Post
{
    private const TABLE = 'posts';

    private const COL_ID       = 'id';
    private const COL_USER_ID  = 'user_id';
    private const COL_COMMENT  = 'comment';

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
            self::TABLE,
            self::COL_USER_ID,
            self::COL_COMMENT
        );

        $stmt = self::db()->prepare($sql);

        try {
            $stmt->execute([$userId, $comment]);
        } catch (PDOException $e) {
            return null;
        }

        return self::findById((int)self::db()->lastInsertId());
    }

    public static function findById(int $id): ?array
    {
        $sql = sprintf(
            "SELECT * FROM %s WHERE %s = ?",
            self::TABLE,
            self::COL_ID
        );

        $stmt = self::db()->prepare($sql);
        $stmt->execute([$id]);

        $post = $stmt->fetch(PDO::FETCH_ASSOC);

        return $post ?: null;
    }
}
