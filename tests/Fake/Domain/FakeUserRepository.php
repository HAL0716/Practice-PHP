<?php

declare(strict_types=1);

namespace Tests\Fake\Domain;

use App\Contracts\Domain\User\UserRepositoryInterface;
use App\Domain\User\User;

final class FakeUserRepository implements UserRepositoryInterface
{
    public ?User $createResult = null;
    public ?User $findByEmailResult = null;
    public ?User $findByIdResult = null;

    public bool $updateResult = true;
    public bool $deleteResult = true;

    public function create(string $name, string $email, string $password): ?User
    {
        return $this->createResult;
    }

    public function findById(int $id): ?User
    {
        return $this->findByIdResult;
    }

    public function findByEmail(string $email): ?User
    {
        return $this->findByEmailResult;
    }

    public function update(int $id, ?string $name = null, ?string $email = null, ?string $password = null): bool
    {
        return $this->updateResult;
    }

    public function delete(int $id): bool
    {
        return $this->deleteResult;
    }
}
