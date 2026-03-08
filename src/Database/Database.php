<?php

declare(strict_types=1);

namespace App\Database;

class Database
{
    private static ?\PDO $connection = null;

    private function __construct()
    {
        throw new \LogicException(
            'Cannot instantiate ' . static::class
        );
    }

    private function __clone()
    {
        throw new \LogicException(
            'Cannot clone ' . static::class
        );
    }

    public static function connect(): \PDO
    {
        if (self::$connection === null) {

            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=%s',
                getenv('DB_HOST'),
                getenv('DB_NAME'),
                getenv('DB_CHARSET') ?: 'utf8mb4'
            );

            self::$connection = new \PDO(
                $dsn,
                getenv('DB_USER'),
                getenv('DB_PASSWORD'),
                [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                ]
            );
        }

        return self::$connection;
    }
}
