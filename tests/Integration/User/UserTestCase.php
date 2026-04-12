<?php

declare(strict_types=1);

namespace Tests\Integration\User;

use Tests\Integration\IntegrationTestCase;

abstract class UserTestCase extends IntegrationTestCase
{
    protected const SIGNUP_URL = '/user/signup';
    protected const SIGNIN_URL = '/user/signin';
    protected const SIGNOUT_URL = '/user/signout';
    protected const UPDATE_URL = '/user/update';
    protected const DELETE_URL = '/user/delete';
    protected const MYPAGE_URL = '/user/mypage';
    protected const HOME_URL = '/post/home';

    // =========================
    // Auth Assertions
    // =========================

    final protected function assertGuest(): void
    {
        $this->assertFalse($this->session()->isLoggedIn());
    }

    final protected function assertAuthenticated(): void
    {
        $this->assertTrue($this->session()->isLoggedIn());
    }
}
