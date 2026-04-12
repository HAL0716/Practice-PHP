<?php

declare(strict_types=1);

namespace Tests\Integration\User;

use App\Application\Http\ResponseInterface;
use App\Domain\User\User;
use App\Domain\User\UserRepositoryInterface;
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

    protected const DEFAULT_EMAIL = 'test@example.com';
    protected const DEFAULT_PASSWORD = 'password123';

    // =========================
    // Setup
    // =========================

    final protected function createUser(): void
    {
        $this->users()->create('Test User', self::DEFAULT_EMAIL, self::DEFAULT_PASSWORD);
    }

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

    // =========================
    // Response Assertions
    // =========================

    final protected function assertError(string $expectedError): void
    {
        $this->assertSame($expectedError, $this->session()->error());
    }

    final protected function assertRedirect(ResponseInterface $response, string $location): void
    {
        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame($location, $response->getHeader('Location'));
    }

    // =========================
    // Helpers
    // =========================

    protected function login(User $user): void
    {
        $this->session()->login($user);
    }

    protected function users(): UserRepositoryInterface
    {
        return $this->container->get(UserRepositoryInterface::class);
    }
}
