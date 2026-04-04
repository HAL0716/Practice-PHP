<?php

declare(strict_types=1);

namespace Tests\Fake\Database;

use App\Contracts\Database\DatabaseInterface;

final class FakeDatabase implements DatabaseInterface
{
    public array $rows = [];
    public int $lastId = 1;

    public function fetchOne(string $sql, array $params = []): ?array
    {
        return $this->rows[0] ?? null;
    }

    public function fetchAll(string $sql, array $params = []): array
    {
        return $this->rows;
    }

    public function execute(string $sql, array $params = []): bool
    {
        return true;
    }

    public function lastInsertId(): int
    {
        return $this->lastId;
    }
}
