<?php

declare(strict_types=1);

use App\Constants\Routes;
use App\Controllers\PostController;
use App\Controllers\UserController;

return [
    Routes::USER_SIGNUP  => [UserController::class, 'signup'],
    Routes::USER_SIGNIN  => [UserController::class, 'signin'],
    Routes::USER_SIGNOUT => [UserController::class, 'signout'],
    Routes::USER_DELETE  => [UserController::class, 'delete'],
    Routes::USER_MYPAGE  => [UserController::class, 'mypage'],

    Routes::POST_HOME   => [PostController::class, 'home'],
    Routes::POST_DELETE => [PostController::class, 'delete'],
];
