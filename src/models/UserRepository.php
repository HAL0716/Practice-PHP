<?php

declare(strict_types=1);

require_once __DIR__ . '/../database/Database.php';
require_once __DIR__ . '/../database/schema/UsersTable.php';
require_once __DIR__ . '/../entities/UserEntity.php';

final class UserRepository
{
    private function __construct()
    {
    }

    private static function db(): PDO
    {
        return Database::connect();
    }

    private static function hydrate(array $row): UserEntity
    {
        return new UserEntity(
            (int)$row[UsersTable::ID],
            $row[UsersTable::USERNAME],
            $row[UsersTable::EMAIL],
            $row[UsersTable::PASSWORD]
        );
    }

    public static function create(
        string $name,
        string $email,
        string $password
    ): ?UserEntity {

        $sql = sprintf(
            "INSERT INTO %s (%s, %s, %s)
             VALUES (?, ?, ?)",
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

        if (!$fields) {
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
            return self::db()->prepare($sql)->execute($params);
        } catch (PDOException) {
            return false;
        }
    }

    public static function findById(int $id): ?UserEntity
    {
        $sql = sprintf(
            "SELECT * FROM %s WHERE %s = ?",
            UsersTable::TABLE,
            UsersTable::ID
        );

        $stmt = self::db()->prepare($sql);
        $stmt->execute([$id]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? self::hydrate($row) : null;
    }

    public static function findByEmail(string $email): ?UserEntity
    {
        $sql = sprintf(
            "SELECT * FROM %s WHERE %s = ?",
            UsersTable::TABLE,
            UsersTable::EMAIL
        );

        $stmt = self::db()->prepare($sql);
        $stmt->execute([$email]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? self::hydrate($row) : null;
    }
}
