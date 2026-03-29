<?php

declare(strict_types=1);

namespace App\Core\Http;

use App\Contracts\Http\RequestInterface;

final class Request implements RequestInterface
{
    public function __construct()
    {
    }

    public function post(string $key, string $default = ''): string
    {
        return $this->value($_POST, $key, $default);
    }

    public function query(string $key, string $default = ''): string
    {
        return $this->value($_GET, $key, $default);
    }

    public function postArray(string $key, array $default = []): array
    {
        return $this->arrayValue($_POST, $key, $default);
    }

    public function queryArray(string $key, array $default = []): array
    {
        return $this->arrayValue($_GET, $key, $default);
    }

    public function isGet(): bool
    {
        return $this->method() === 'GET';
    }

    public function isPost(): bool
    {
        return $this->method() === 'POST';
    }

    public function path(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';

        return parse_url($uri, PHP_URL_PATH) ?: '/';
    }

    private function value(array $source, string $key, string $default): string
    {
        if (!isset($source[$key]) || is_array($source[$key])) {
            return $default;
        }

        return (string) $source[$key];
    }

    private function arrayValue(array $source, string $key, array $default): array
    {
        if (!isset($source[$key]) || !is_array($source[$key])) {
            return $default;
        }

        return $source[$key];
    }

    private function method(): string
    {
        return $_SERVER['REQUEST_METHOD'] ?? 'GET';
    }
}
