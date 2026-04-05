<?php

declare(strict_types=1);

namespace App\Contracts\Domain\User;

use App\Domain\User\User;

interface UserRepositoryInterface
{
    public function create(string $name, string $email, string $password): ?User;

    public function findById(int $id): ?User;

    public function findByEmail(string $email): ?User;

    public function update(int $id, ?string $name = null, ?string $email = null, ?string $password = null): bool;

    public function delete(int $id): bool;
}
