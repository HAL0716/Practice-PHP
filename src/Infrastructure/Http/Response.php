<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

use App\Application\Http\ResponseInterface;

final class Response implements ResponseInterface
{
    private $headerSender;

    public function __construct(?callable $headerSender = null)
    {
        $this->headerSender = $headerSender ?? fn (string $header) => header($header);
    }

    public function redirect(string $url): void
    {
        if (!headers_sent()) {
            ($this->headerSender)("Location: {$url}");
        }
    }
}
