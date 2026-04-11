<?php

declare(strict_types=1);

namespace App\Application;

use App\Application\Http\RequestInterface;
use App\Bootstrap\Container;

final class App
{
    public function __construct(
        private RequestInterface $request,
        private array $routes,
        private Container $container
    ) {}

    public function run(): void
    {
        $path = $this->request->path();

        if (!isset($this->routes[$path])) {
            $this->notFound();
            return;
        }

        [$controllerClass, $method] = $this->routes[$path];

        $controller = $this->container->get($controllerClass);

        $controller->$method();
    }

    private function notFound(): void
    {
        http_response_code(404);
        echo '404 Not Found';
    }
}
