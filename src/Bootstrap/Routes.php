<?php

declare(strict_types=1);

namespace App\Bootstrap;

use App\Application\Constants\RoutePaths;
use App\Application\Controllers\PostController;
use App\Application\Controllers\UserController;

final class Routes
{
    private function __construct()
    {
    }

    public static function definitions(): array
    {
        return [
            RoutePaths::USER_SIGNUP  => [UserController::class, 'signup'],
            RoutePaths::USER_SIGNIN  => [UserController::class, 'signin'],
            RoutePaths::USER_SIGNOUT => [UserController::class, 'signout'],
            RoutePaths::USER_DELETE  => [UserController::class, 'delete'],
            RoutePaths::USER_MYPAGE  => [UserController::class, 'mypage'],

            RoutePaths::POST_HOME   => [PostController::class, 'home'],
            RoutePaths::POST_DELETE => [PostController::class, 'delete'],
        ];
    }
}
