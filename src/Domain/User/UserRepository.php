<?php

declare(strict_types=1);

namespace App\Domain\User;

use App\Core\Repository;
use App\Database\Schema\UsersTable;

final class UserRepository extends Repository
{
    protected function hydrate(array $row): User
    {
        return new User(
            (int) $row[UsersTable::ID],
            $row[UsersTable::USERNAME],
            $row[UsersTable::EMAIL],
            $row[UsersTable::PASSWORD]
        );
    }

    protected function baseSelect(): string
    {
        return sprintf("SELECT * FROM %s", UsersTable::TABLE);
    }

    public function create(string $name, string $email, string $password): ?User
    {
        $sql = sprintf(
            "INSERT INTO %s (%s, %s, %s) VALUES (?, ?, ?)",
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

        if (!$this->execute($sql, $params)) {
            return null;
        }

        return self::findById((int) $this->db->lastInsertId());
    }

    public function findById(int $id): ?User
    {
        return self::findOneBy(UsersTable::ID, [$id]);
    }

    public function findByEmail(string $email): ?User
    {
        return self::findOneBy(UsersTable::EMAIL, [$email]);
    }

    public function update(int $id, ?string $name = null, ?string $email = null, ?string $password = null): bool
    {
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

        return $this->execute($sql, $params);
    }

    public function delete(int $id): bool
    {
        $sql = sprintf(
            "DELETE FROM %s WHERE %s = ?",
            UsersTable::TABLE,
            UsersTable::ID
        );

        return $this->execute($sql, [$id]);
    }
}
