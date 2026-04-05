<?php

declare(strict_types=1);

namespace Tests\Fake\Infrastructure\Http;

use App\Application\Http\RequestInterface;

final class FakeRequest implements RequestInterface
{
    public function __construct(private array $post = [], private array $query = [], private string $method = 'GET', private string $path = '/')
    {
    }

    public function post(string $key, string $default = ''): string
    {
        if (!isset($this->post[$key]) || is_array($this->post[$key])) {
            return $default;
        }

        return (string) $this->post[$key];
    }

    public function query(string $key, string $default = ''): string
    {
        if (!isset($this->query[$key]) || is_array($this->query[$key])) {
            return $default;
        }

        return (string) $this->query[$key];
    }

    public function postArray(string $key, array $default = []): array
    {
        if (!isset($this->post[$key]) || !is_array($this->post[$key])) {
            return $default;
        }

        return $this->post[$key];
    }

    public function queryArray(string $key, array $default = []): array
    {
        if (!isset($this->query[$key]) || !is_array($this->query[$key])) {
            return $default;
        }

        return $this->query[$key];
    }

    public function isGet(): bool
    {
        return $this->method === 'GET';
    }

    public function isPost(): bool
    {
        return $this->method === 'POST';
    }

    public function path(): string
    {
        return $this->path;
    }
}
