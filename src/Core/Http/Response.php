<?php

declare(strict_types=1);

namespace App\Core\Http;

use App\Contracts\Http\ResponseInterface;

final class Response implements ResponseInterface
{
    public function __construct()
    {
    }

    public function redirect(string $url): void
    {
        if (!headers_sent()) {
            header("Location: {$url}");
        }
    }
}
