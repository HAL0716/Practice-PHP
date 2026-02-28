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

    private const DUMMY_HASH = '$2y$10$usesomesillystringfore7hnbRJHxXVLeakoG8K30oukPsA.ztMG';

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
            return null;
        }

        return self::findByEmail($email);
    }

    public static function update(
        int $id,
        ?string $name = null,
        ?string $email = null,
        ?string $password = null
    ): bool {
        $fields = [];
        $params = [];

        if ($name !== null) {
            $fields[] = self::COL_NAME . ' = ?';
            $params[] = $name;
        }
        if ($email !== null) {
            $fields[] = self::COL_EMAIL . ' = ?';
            $params[] = $email;
        }
        if ($password !== null) {
            $fields[] = self::COL_PASSWORD . ' = ?';
            $params[] = password_hash($password, PASSWORD_DEFAULT);
        }

        if (empty($fields)) {
            return false;
        }

        $params[] = $id;

        $sql = sprintf(
            "UPDATE %s SET %s WHERE %s = ?",
            self::TABLE,
            implode(', ', $fields),
            self::COL_ID
        );

        try {
            $stmt = self::db()->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            return false;
        }
    }

    public static function findById(int $id): ?array
    {
        $sql = sprintf(
            "SELECT %s, %s, %s, %s FROM %s WHERE %s = ?",
            self::COL_ID,
            self::COL_NAME,
            self::COL_EMAIL,
            self::COL_PASSWORD,
            self::TABLE,
            self::COL_ID
        );

        $stmt = self::db()->prepare($sql);
        $stmt->execute([$id]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user !== false ? $user : null;
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
