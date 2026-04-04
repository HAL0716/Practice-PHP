<?php

declare(strict_types=1);

namespace App\Contracts\Database;

interface DatabaseInterface
{
    public function fetchOne(string $sql, array $params = []): ?array;

    public function fetchAll(string $sql, array $params = []): array;

    public function execute(string $sql, array $params = []): bool;

    public function lastInsertId(): int;
}
