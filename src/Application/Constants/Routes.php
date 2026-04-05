<?php

declare(strict_types=1);

namespace App\Application\Constants;

final class Routes
{
    public const USER_SIGNUP  = '/user/signup';
    public const USER_SIGNIN  = '/user/signin';
    public const USER_SIGNOUT = '/user/signout';
    public const USER_DELETE  = '/user/delete';
    public const USER_MYPAGE  = '/user/mypage';

    public const POST_HOME   = '/post/home';
    public const POST_DELETE = '/post/delete';

    private function __construct()
    {
        throw new \LogicException(
            'Cannot instantiate ' . static::class
        );
    }
}
