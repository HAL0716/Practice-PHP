<?php

declare(strict_types=1);

require_once __DIR__ . '/../database/Database.php';
require_once __DIR__ . '/../database/schema/UsersTable.php';

final class User
{
    public const FIELD_ID       = 'id';
    public const FIELD_USERNAME = 'username';
    public const FIELD_EMAIL    = 'email';
    public const FIELD_PASSWORD = 'password';

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
            UsersTable::TABLE,
            UsersTable::USERNAME,
            UsersTable::EMAIL,
            UsersTable::PASSWORD
        );

        $stmt = self::db()->prepare($sql);

        try {
            $stmt->execute([
                $name,
                $email,
                password_hash($password, PASSWORD_DEFAULT)
            ]);
        } catch (PDOException) {
            return null;
        }

        return self::findById((int)self::db()->lastInsertId());
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
            $fields[] = UsersTable::USERNAME . ' = ?';
            $params[] = $name;
        }
        if ($email !== null) {
            $fields[] = UsersTable::EMAIL . ' = ?';
            $params[] = $email;
        }
        if ($password !== null) {
            $fields[] = UsersTable::PASSWORD . ' = ?';
            $params[] = password_hash($password, PASSWORD_DEFAULT);
        }

        if (empty($fields)) {
            return false;
        }

        $params[] = $id;

        $sql = sprintf(
            "UPDATE %s SET %s WHERE %s = ?",
            UsersTable::TABLE,
            implode(', ', $fields),
            UsersTable::ID
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
            "SELECT
                %s AS %s,
                %s AS %s,
                %s AS %s
             FROM %s
             WHERE %s = ?",
            UsersTable::ID,
            User::FIELD_ID,
            UsersTable::USERNAME,
            User::FIELD_USERNAME,
            UsersTable::EMAIL,
            User::FIELD_EMAIL,
            UsersTable::TABLE,
            UsersTable::ID
        );

        $stmt = self::db()->prepare($sql);
        $stmt->execute([$id]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    public static function findByEmail(string $email): ?array
    {
        $sql = sprintf(
            "SELECT
                %s AS %s,
                %s AS %s,
                %s AS %s,
                %s AS %s
             FROM %s
             WHERE %s = ?",
            UsersTable::ID,
            User::FIELD_ID,
            UsersTable::USERNAME,
            User::FIELD_USERNAME,
            UsersTable::EMAIL,
            User::FIELD_EMAIL,
            UsersTable::PASSWORD,
            User::FIELD_PASSWORD,
            UsersTable::TABLE,
            UsersTable::EMAIL
        );

        $stmt = self::db()->prepare($sql);
        $stmt->execute([$email]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user !== false ? $user : null;
    }

    public static function verifyPassword(string $email, string $password): bool
    {
        $user = self::findByEmail($email);

        $hash = $user[User::FIELD_PASSWORD] ?? self::DUMMY_HASH;

        return password_verify($password, $hash);
    }
}
