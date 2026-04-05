<?php

declare(strict_types=1);

use App\Core\Http\Request;
use App\Core\Http\Session;
use App\Core\Http\Response;
use App\Core\Security\Csrf;
use App\Core\Security\LoginThrottle;
use App\Database\Database;
use App\Domain\User\UserRepository;
use App\Domain\Post\PostRepository;
use App\Controllers\UserController;
use App\Controllers\PostController;
use App\Core\Container\Container;

$container = new Container();

$container->set(Request::class, fn () => new Request());

$container->set(Session::class, function () {
    $session = new Session();
    $session->init();
    return $session;
});

$container->set(Response::class, fn () => new Response());

$container->set(Csrf::class, fn ($c) => new Csrf($c->get(Session::class)));

$container->set(LoginThrottle::class, fn ($c) => new LoginThrottle($c->get(Session::class)));

$container->set(Database::class, fn () => new Database());

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
