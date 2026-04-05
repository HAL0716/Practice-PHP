<?php

declare(strict_types=1);

namespace App\Contracts\Http;

interface ResponseInterface
{
    public function redirect(string $url): void;
}
