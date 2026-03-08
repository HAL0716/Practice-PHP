<?php

declare(strict_types=1);

namespace App\Core;

use App\Database\Database;

abstract class Repository
{
    final private function __construct()
    {
        throw new \LogicException(
            'Cannot instantiate ' . static::class
        );
    }

    final protected static function db(): \PDO
    {
        return Database::connect();
    }

    abstract protected static function hydrate(array $row): object;

    abstract protected static function baseSelect(): string;

    final protected static function execute(
        string $sql,
        array $params = []
    ): \PDOStatement {

        $stmt = static::db()->prepare($sql);
        $stmt->execute($params);

        return $stmt;
    }

    final protected static function fetchOne(
        string $sql,
        array $params = []
    ): ?object {

        $row = static::execute($sql, $params)
            ->fetch(\PDO::FETCH_ASSOC);

        return $row ? static::hydrate($row) : null;
    }

    final protected static function fetchAll(
        string $sql,
        array $params = []
    ): array {

        $rows = static::execute($sql, $params)
            ->fetchAll(\PDO::FETCH_ASSOC);

        return array_map(
            fn (array $row) => static::hydrate($row),
            $rows
        );
    }

    final protected static function findOneBy(
        string $where,
        array $params
    ): ?object {

        $sql = static::baseSelect()
            . sprintf(" WHERE %s = ?", $where);

        return static::fetchOne($sql, $params);
    }

    final protected static function findAllOrdered(
        string $orderBy,
        string $direction = 'ASC'
    ): array {

        $direction = strtoupper($direction);

        if (!in_array($direction, ['ASC', 'DESC'], true)) {
            throw new \InvalidArgumentException('Invalid order direction');
        }

        $sql = static::baseSelect()
            . sprintf(" ORDER BY %s %s", $orderBy, $direction);

        return static::fetchAll($sql);
    }
}
