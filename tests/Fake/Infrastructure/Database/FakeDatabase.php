<?php

declare(strict_types=1);

namespace Tests\Fake\Infrastructure\Database;

use App\Infrastructure\Database\DatabaseInterface;

final class FakeDatabase implements DatabaseInterface
{
    public array $rows = [];
    public int $lastId = 1;

    public bool $shouldFail = false;
    public bool $inTransaction = false;

    public function beginTransaction(): void
    {
        $this->inTransaction = true;
    }

    public function rollBack(): void
    {
        $this->inTransaction = false;
    }

    public function fetchOne(string $sql, array $params = []): ?array
    {
        if (empty($this->rows)) {
            return null;
        }

        // WHERE xxx = ?
        if (preg_match('/WHERE\s+([\w\.]+)\s*=\s*\?/i', $sql, $matches)) {
            $column = $this->normalizeColumn($matches[1]);
            $value = $params[0] ?? null;

            foreach ($this->rows as $row) {
                if (($row[$column] ?? null) === $value) {
                    return $row;
                }
            }

            return null;
        }

        return $this->rows[0] ?? null;
    }

    public function fetchAll(string $sql, array $params = []): array
    {
        $rows = $this->rows;

        // ORDER BY
        if (preg_match('/ORDER BY\s+([\w\.]+)\s+(ASC|DESC)/i', $sql, $matches)) {
            $column = $this->normalizeColumn($matches[1]);
            $direction = strtoupper($matches[2]);

            usort($rows, function ($a, $b) use ($column, $direction) {
                $aVal = $a[$column] ?? null;
                $bVal = $b[$column] ?? null;

                if ($aVal === $bVal) {
                    return 0;
                }

                return $direction === 'ASC'
                    ? $aVal <=> $bVal
                    : $bVal <=> $aVal;
            });
        }

        return $rows;
    }

    public function execute(string $sql, array $params = []): bool
    {
        if ($this->shouldFail) {
            return false;
        }

        return true;
    }

    public function lastInsertId(): int
    {
        return $this->lastId;
    }

    private function normalizeColumn(string $column): string
    {
        if (str_contains($column, '.')) {
            return explode('.', $column)[1];
        }

        return $column;
    }
}
