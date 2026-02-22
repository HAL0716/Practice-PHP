<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/Database.php';

final class User
{
    private const TABLE = 'users';

    private const COL_ID       = 'id';
    private const COL_NAME     = 'name';
    private const COL_EMAIL    = 'email';
    private const COL_PASSWORD = 'password';

    private const DUMMY_HASH = '$2y$10$usesomesillystringfore7hnbRJHxXVLeakoG8K30oukPsA.ztMG'; // タイミング攻撃対策用のダミーハッシュ

    private function __construct()
    {
    }

    private static function db(): PDO
    {
        return Database::connect();
    }

    public static function create(
        string $name,
        string $email,
        string $password
    ): ?array {
        $sql = sprintf(
            "INSERT INTO %s (%s, %s, %s) VALUES (?, ?, ?)",
            self::TABLE,
            self::COL_NAME,
            self::COL_EMAIL,
            self::COL_PASSWORD
        );

        $stmt = self::db()->prepare($sql);

        $hashed = password_hash($password, PASSWORD_DEFAULT);

        try {
            $stmt->execute([$name, $email, $hashed]);
        } catch (PDOException $e) {
            return null; // email重複など
        }

        return self::findByEmail($email);
    }

    public static function findByEmail(string $email): ?array
    {
        $sql = sprintf(
            "SELECT %s, %s, %s, %s FROM %s WHERE %s = ?",
            self::COL_ID,
            self::COL_NAME,
            self::COL_EMAIL,
            self::COL_PASSWORD,
            self::TABLE,
            self::COL_EMAIL
        );

        $stmt = self::db()->prepare($sql);
        $stmt->execute([$email]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user !== false ? $user : null;
    }

    public static function verifyPassword(string $email, string $password): bool
    {
        $user = self::findByEmail($email);

        $hash = $user[self::COL_PASSWORD] ?? self::DUMMY_HASH;

        return password_verify($password, $hash);
    }
}
