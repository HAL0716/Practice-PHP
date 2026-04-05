<?php

declare(strict_types=1);

use App\Application\Controllers\UserController;
use App\Application\Controllers\PostController;
use App\Bootstrap\Container;
use App\Infrastructure\Http\Request;
use App\Infrastructure\Http\Session;
use App\Infrastructure\Http\Response;
use App\Infrastructure\Security\Csrf;
use App\Infrastructure\Security\LoginThrottle;
use App\Infrastructure\Database\Database;
use App\Infrastructure\Persistence\PostRepository;
use App\Infrastructure\Persistence\UserRepository;

$container = new Container();

$container->set(Request::class, fn ($c) => new Request());

$container->set(Session::class, function ($c) {
    $session = new Session();
    $session->init();
    return $session;
});

$container->set(Response::class, fn ($c) => new Response());

$container->set(Csrf::class, fn ($c) => new Csrf($c->get(Session::class)));

$container->set(LoginThrottle::class, fn ($c) => new LoginThrottle($c->get(Session::class)));

$container->set(Database::class, fn ($c) => new Database());

$container->set(UserRepository::class, fn ($c) => new UserRepository($c->get(Database::class)));

$container->set(PostRepository::class, fn ($c) =>    new PostRepository($c->get(Database::class)));

$container->set(
    UserController::class,
    fn ($c) =>
    new UserController(
        $c->get(Request::class),
        $c->get(Session::class),
        $c->get(Response::class),
        $c->get(Csrf::class),
        $c->get(UserRepository::class),
        $c->get(LoginThrottle::class),
    )
);

$container->set(
    PostController::class,
    fn ($c) =>
    new PostController(
        $c->get(Request::class),
        $c->get(Session::class),
        $c->get(Response::class),
        $c->get(Csrf::class),
        $c->get(PostRepository::class),
    )
);

return $container;
