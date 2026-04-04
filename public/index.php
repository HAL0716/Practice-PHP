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
use App\Constants\Routes;
use App\Controllers\UserController;
use App\Controllers\PostController;

require_once __DIR__ . '/../vendor/autoload.php';

$routes = [
    Routes::USER_SIGNUP  => [UserController::class, 'signup'],
    Routes::USER_SIGNIN  => [UserController::class, 'signin'],
    Routes::USER_SIGNOUT => [UserController::class, 'signout'],
    Routes::USER_DELETE  => [UserController::class, 'delete'],
    Routes::USER_MYPAGE  => [UserController::class, 'mypage'],

    Routes::POST_HOME   => [PostController::class, 'home'],
    Routes::POST_DELETE => [PostController::class, 'delete'],
];

$db = new Database();

$request = new Request();
$session = new Session();
$response = new Response();
$csrf = new Csrf($session);

$userRepository = new UserRepository($db);
$postRepository = new PostRepository($db);

$throttle = new LoginThrottle($session);

$session->init();
$url = $request->path();

if (!isset($routes[$url])) {
    http_response_code(404);
    echo '404 Not Found';
    exit;
}

[$controllerClass, $method] = $routes[$url];

$controller = match ($controllerClass) {
    UserController::class => new UserController(
        $request,
        $session,
        $response,
        $csrf,
        $userRepository,
        $throttle
    ),

    PostController::class => new PostController(
        $request,
        $session,
        $response,
        $csrf,
        $postRepository
    ),

    default => throw new RuntimeException('Unknown controller')
};

$controller->$method();
