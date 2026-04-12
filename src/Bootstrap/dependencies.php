<?php

declare(strict_types=1);

use App\Application\App;
use App\Application\Controllers\UserController;
use App\Application\Controllers\PostController;
use App\Application\Http\RequestInterface;
use App\Application\Http\ResponseInterface;
use App\Application\Http\SessionInterface;
use App\Application\Router;
use App\Application\Security\CsrfInterface;
use App\Application\Security\LoginThrottleInterface;
use App\Bootstrap\Container;
use App\Bootstrap\Routes;
use App\Domain\Post\PostRepositoryInterface;
use App\Domain\User\UserRepositoryInterface;
use App\Infrastructure\Http\Request;
use App\Infrastructure\Http\Session;
use App\Infrastructure\Http\Response;
use App\Infrastructure\Security\Csrf;
use App\Infrastructure\Security\LoginThrottle;
use App\Infrastructure\Database\Database;
use App\Infrastructure\Database\DatabaseInterface;
use App\Infrastructure\Http\ResponseEmitter;
use App\Infrastructure\Persistence\PostRepository;
use App\Infrastructure\Persistence\UserRepository;

$container = new Container();

$container->set(Routes::class, function ($c) {
    return Routes::definitions();
});

$container->set(Router::class, function ($c) {
    return new Router($c->get(Routes::class));
});

$container->set(RequestInterface::class, fn ($c) => new Request());

$container->set(SessionInterface::class, function ($c) {
    $session = new Session();
    $session->init();
    return $session;
});

$container->set(ResponseInterface::class, fn ($c) => new Response());

$container->set(ResponseEmitter::class, fn ($c) => new ResponseEmitter());

$container->set(CsrfInterface::class, fn ($c) => new Csrf($c->get(SessionInterface::class)));

$container->set(LoginThrottleInterface::class, fn ($c) => new LoginThrottle($c->get(SessionInterface::class)));

$container->set(DatabaseInterface::class, fn ($c) => new Database());

$container->set(UserRepositoryInterface::class, fn ($c) => new UserRepository($c->get(DatabaseInterface::class)));

$container->set(PostRepositoryInterface::class, fn ($c) => new PostRepository($c->get(DatabaseInterface::class)));

$container->set(
    UserController::class,
    fn ($c) =>
    new UserController(
        $c->get(RequestInterface::class),
        $c->get(SessionInterface::class),
        $c->get(CsrfInterface::class),
        $c->get(UserRepositoryInterface::class),
        $c->get(LoginThrottleInterface::class),
    )
);

$container->set(
    PostController::class,
    fn ($c) =>
    new PostController(
        $c->get(RequestInterface::class),
        $c->get(SessionInterface::class),
        $c->get(CsrfInterface::class),
        $c->get(PostRepositoryInterface::class),
    )
);

$container->set(App::class, function ($c) {
    return new App(
        $c->get(RequestInterface::class),
        $c->get(Router::class),
        $c
    );
});

return $container;
