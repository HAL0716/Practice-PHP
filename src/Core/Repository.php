<?php

declare(strict_types=1);

namespace App\Core;

use App\Contracts\Database\DatabaseInterface;

abstract class Repository
{
    public function __construct(protected DatabaseInterface $db)
    {
    }

    abstract protected function hydrate(array $row): object;

    abstract protected function baseSelect(): string;

    final protected function fetchOne(string $sql, array $params = []): ?object
    {
        $row = $this->db->fetchOne($sql, $params);

        return $row ? $this->hydrate($row) : null;
    }

    final protected function fetchAll(string $sql, array $params = []): array
    {
        $rows = $this->db->fetchAll($sql, $params);

        return array_map(fn(array $row) => $this->hydrate($row), $rows);
    }

    final protected function execute(string $sql, array $params = []): bool
    {
        return $this->db->execute($sql, $params);
    }

    final protected function findOneBy(string $where, array $params): ?object
    {
        $sql = $this->baseSelect() . sprintf(" WHERE %s = ?", $where);

        return $this->fetchOne($sql, $params);
    }

    final protected function findAllOrdered(string $orderBy, string $direction = 'ASC'): array
    {
        $direction = strtoupper($direction);

        if (!in_array($direction, ['ASC', 'DESC'], true)) {
            throw new \InvalidArgumentException(sprintf('Invalid order direction "%s".', $direction));
        }

        $sql = $this->baseSelect() . sprintf(" ORDER BY %s %s", $orderBy, $direction);

        return $this->fetchAll($sql);
    }
}
