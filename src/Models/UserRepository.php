<?php

declare(strict_types=1);

namespace App\Models;

final class UserRepository extends \App\Core\Repository
{
    protected static function hydrate(array $row): \App\Entities\UserEntity
    {
        return new \App\Entities\UserEntity(
            (int)$row[\App\Database\Schema\UsersTable::ID],
            $row[\App\Database\Schema\UsersTable::USERNAME],
            $row[\App\Database\Schema\UsersTable::EMAIL],
            $row[\App\Database\Schema\UsersTable::PASSWORD]
        );
    }

    protected static function baseSelect(): string
    {
        return sprintf(
            "SELECT * FROM %s",
            \App\Database\Schema\UsersTable::TABLE
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
            \App\Database\Schema\UsersTable::TABLE,
            \App\Database\Schema\UsersTable::USERNAME,
            \App\Database\Schema\UsersTable::EMAIL,
            \App\Database\Schema\UsersTable::PASSWORD
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
            $fields[] = \App\Database\Schema\UsersTable::USERNAME . ' = ?';
            $params[] = $name;
        }

        if ($email !== null) {
            $fields[] = \App\Database\Schema\UsersTable::EMAIL . ' = ?';
            $params[] = $email;
        }

        if ($password !== null) {
            $fields[] = \App\Database\Schema\UsersTable::PASSWORD . ' = ?';
            $params[] = password_hash($password, PASSWORD_DEFAULT);
        }

        if (!$fields) {
            return false;
        }

        $params[] = $id;

        $sql = sprintf(
            "UPDATE %s SET %s WHERE %s = ?",
            \App\Database\Schema\UsersTable::TABLE,
            implode(', ', $fields),
            \App\Database\Schema\UsersTable::ID
        );

        try {
            self::execute($sql, $params);
            return true;
        } catch (\PDOException) {
            return false;
        }
    }

    public static function findById(int $id): ?\App\Entities\UserEntity
    {
        return self::findOneBy(\App\Database\Schema\UsersTable::ID, [$id]);
    }

    public static function findByEmail(string $email): ?\App\Entities\UserEntity
    {
        return self::findOneBy(\App\Database\Schema\UsersTable::EMAIL, [$email]);
    }
}
