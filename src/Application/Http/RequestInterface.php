<?php

declare(strict_types=1);

namespace App\Application\Http;

interface RequestInterface
{
    public function post(string $key, string $default = ''): string;

    public function query(string $key, string $default = ''): string;

    public function postArray(string $key, array $default = []): array;

    public function queryArray(string $key, array $default = []): array;

    public function isGet(): bool;

    public function isPost(): bool;

    public function path(): string;
}
