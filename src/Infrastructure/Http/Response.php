<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

use App\Application\Http\ResponseInterface;

final class Response implements ResponseInterface
{
    public function __construct(
        private int $statusCode = 200,
        private array $headers = [],
        private string $body = ''
    ) {
    }

    public static function redirect(string $url): self
    {
        return new self(302, ['Location' => $url]);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getHeader(string $name): ?string
    {
        return $this->headers[$name] ?? null;
    }

    public function getBody(): string
    {
        return $this->body;
    }
}
