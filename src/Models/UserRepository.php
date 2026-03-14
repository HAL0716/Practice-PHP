<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Repository;
use App\Entities\UserEntity;
use App\Database\Schema\UsersTable;

final class UserRepository extends Repository
{
    protected static function hydrate(array $row): UserEntity
    {
        return new UserEntity(
            (int)$row[UsersTable::ID],
            $row[UsersTable::USERNAME],
            $row[UsersTable::EMAIL],
            $row[UsersTable::PASSWORD]
        );
    }

    protected static function baseSelect(): string
    {
        return sprintf(
            "SELECT * FROM %s",
            UsersTable::TABLE
        );
    }

    public static function create(
        string $name,
        string $email,
        string $password
    ): ?\App\Entities\UserEntity {

        $db = self::db();

        $sql = sprintf(
            "INSERT INTO %s (%s, %s, %s)
             VALUES (?, ?, ?)",
            UsersTable::TABLE,
            UsersTable::USERNAME,
            UsersTable::EMAIL,
            UsersTable::PASSWORD
        );

        $params = [
            $name,
            $email,
            password_hash($password, PASSWORD_DEFAULT)
        ];

        try {
            self::execute($sql, $params);
        } catch (\PDOException) {
            return null;
        }

        return self::findById((int)$db->lastInsertId());
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
            self::execute($sql, $params);
            return true;
        } catch (\PDOException) {
            return false;
        }
    }

    public static function findById(int $id): ?UserEntity
    {
        return self::findOneBy(UsersTable::ID, [$id]);
    }

    public static function findByEmail(string $email): ?UserEntity
    {
        return self::findOneBy(UsersTable::EMAIL, [$email]);
    }

    public static function delete(int $id): bool
    {
        $sql = sprintf(
            "DELETE FROM %s WHERE %s = ?",
            UsersTable::TABLE,
            UsersTable::ID
        );

        try {
            self::execute($sql, [$id]);
            return true;
        } catch (\PDOException) {
            return false;
        }
    }
}
