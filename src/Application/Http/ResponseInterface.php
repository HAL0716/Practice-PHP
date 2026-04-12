<?php

declare(strict_types=1);

namespace App\Application\Http;

interface ResponseInterface
{
    public function getStatusCode(): int;

    public function getHeaders(): array;

    public function getHeader(string $name): ?string;

    public function getBody(): string;
}
