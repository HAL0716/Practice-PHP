<?php

declare(strict_types=1);

namespace App\Entities;

final class UserEntity
{
    public function __construct(
        private int $id,
        private string $username,
        private string $email,
        private string $passwordHash
    ) {
    }

    public function id(): int
    {
        return $this->id;
    }

    public function username(): string
    {
        return $this->username;
    }

    public function email(): string
    {
        return $this->email;
    }

    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->passwordHash);
    }
}
