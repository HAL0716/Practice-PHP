<?php

declare(strict_types=1);

namespace App\Application;

use App\Application\Http\RequestInterface;
use App\Application\Http\ResponseInterface;
use App\Bootstrap\Container;
use App\Infrastructure\Http\Response;

final class App
{
    public function __construct(
        private RequestInterface $request,
        private Router $router,
        private Container $container
    ) {
    }

    public function run(): ResponseInterface
    {
        $route = $this->router->resolve($this->request);

        if ($route === null) {
            return $this->notFound();
        }

        [$controllerClass, $method] = $route;

        $controller = $this->container->get($controllerClass);

        return $controller->$method();
    }

    private function notFound(): ResponseInterface
    {
        return new Response(404, [], '404 Not Found');
    }
}
