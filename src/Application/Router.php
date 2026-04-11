<?php

declare(strict_types=1);

namespace App\Application;

use App\Application\Http\RequestInterface;

final class Router
{
    public function __construct(
        private array $routes
    ) {}

    public function resolve(RequestInterface $request): ?array
    {
        return $this->routes[$request->path()] ?? null;
    }
}
