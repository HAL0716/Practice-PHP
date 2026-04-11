<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

use App\Application\Http\ResponseInterface;

final class ResponseEmitter
{
    public function emit(ResponseInterface $response): void
    {
        http_response_code($response->getStatusCode());

        foreach ($response->getHeaders() as $name => $value) {
            header("{$name}: {$value}");
        }

        echo $response->getBody();
    }
}
