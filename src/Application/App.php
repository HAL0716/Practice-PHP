<?php

declare(strict_types=1);

namespace App\Application;

use App\Application\Http\RequestInterface;
use App\Bootstrap\Container;

final class App
{
    public function __construct(
        private RequestInterface $request,
        private Router $router,
        private Container $container
    ) {}

    public function run(): void
    {
        $route = $this->router->resolve($this->request);

        if ($route === null) {
            $this->notFound();
            return;
        }

        [$controllerClass, $method] = $route;

        $controller = $this->container->get($controllerClass);

        $controller->$method();
    }

    private function notFound(): void
    {
        http_response_code(404);
        echo '404 Not Found';
    }
}
