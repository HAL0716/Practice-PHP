<?php

declare(strict_types=1);

function getDb(): PDO
{
    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=%s',
        getenv('DB_HOST'),
        getenv('DB_NAME'),
        getenv('DB_CHARSET')
    );

    $username = getenv('DB_USER');
    $password = getenv('DB_PASSWORD');

    try {
        $dbh = new PDO(
            $dsn,
            $username,
            $password,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        return $dbh;
    } catch (PDOException $e) {
        exit('DB接続失敗: ' . $e->getMessage());
    }
}
