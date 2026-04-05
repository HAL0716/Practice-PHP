<?php

declare(strict_types=1);

namespace Tests\Core;

use App\Infrastructure\Persistence\Repository;

final class TestRepository extends Repository
{
    protected function hydrate(array $row): object
    {
        return (object) $row;
    }

    protected function baseSelect(): string
    {
        return 'SELECT * FROM test';
    }

    public function findOne(string $where, array $params): ?object
    {
        return $this->findOneBy($where, $params);
    }

    public function findAll(string $orderBy, string $direction = 'ASC'): array
    {
        return $this->findAllOrdered($orderBy, $direction);
    }
}
