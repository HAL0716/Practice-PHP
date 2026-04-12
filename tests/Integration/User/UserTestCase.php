<?php

declare(strict_types=1);

namespace Tests\Integration\User;

use Tests\Integration\Auth\AuthTestCase;
use App\Application\Http\ResponseInterface;

abstract class UserTestCase extends AuthTestCase
{
    protected const SIGNUP_URL = '/user/signup';
    protected const SIGNOUT_URL = '/user/signout';
    protected const DELETE_URL = '/user/delete';
    protected const MYPAGE_URL = '/user/mypage';

    // =========================
    // Request Helper
    // =========================

    protected function postSignup(array $override = []): ResponseInterface
    {
        return $this->post(
            self::SIGNUP_URL,
            array_merge([
                'token' => $this->csrfToken(),
                'name' => self::DEFAULT_NAME,
                'mail' => self::DEFAULT_EMAIL,
                'pass' => self::DEFAULT_PASSWORD,
                'pass_confirm' => self::DEFAULT_PASSWORD,
            ], $override)
        );
    }

    protected function postSignin(array $override = []): ResponseInterface
    {
        return $this->post(
            self::SIGNIN_URL,
            array_merge([
                'token' => $this->csrfToken(),
                'mail' => self::DEFAULT_EMAIL,
                'pass' => self::DEFAULT_PASSWORD,
            ], $override)
        );
    }

    protected function postUpdate(array $override = []): ResponseInterface
    {
        return $this->post(
            self::MYPAGE_URL,
            array_merge([
                'token' => $this->csrfToken(),
                'name' => 'Test User',
                'mail' => self::DEFAULT_EMAIL,
                'pass_current' => self::DEFAULT_PASSWORD,
            ], $override)
        );
    }

    protected function postDelete(array $override = [])
    {
        return $this->post(
            self::DELETE_URL,
            array_merge([
                'token' => $this->csrfToken(),
                'pass_current' => self::DEFAULT_PASSWORD,
            ], $override)
        );
    }
}
