<?php

declare(strict_types=1);

namespace App\Constants;

final class Routes
{
    public const HOME    = '/home';
    public const SIGNUP  = '/signup';
    public const SIGNIN  = '/signin';
    public const SIGNOUT = '/signout';
    public const MYPAGE  = '/mypage';

    private function __construct()
    {
        throw new \LogicException(
            'Cannot instantiate ' . static::class
        );
    }
}
