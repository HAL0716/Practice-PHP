<?php

declare(strict_types=1);

namespace Tests\Integration\Auth;

use App\Domain\User\User;
use Tests\Integration\IntegrationTestCase;

abstract class AuthTestCase extends IntegrationTestCase
{
    protected const DEFAULT_NAME = 'Test User';
    protected const DEFAULT_EMAIL = 'test@example.com';
    protected const DEFAULT_PASSWORD = 'password123';

    protected function createUser(array $overrides = []): User
    {
        $name = $overrides['username'] ?? self::DEFAULT_NAME;
        $email = $overrides['email'] ?? self::DEFAULT_EMAIL;
        $password = $overrides['password'] ?? self::DEFAULT_PASSWORD;

        return $this->users()->create($name, $email, $password);
    }

    protected function login(User $user): void
    {
        $this->session()->login($user);
    }

    protected function loginAsUser(?User $user = null): User
    {
        $user ??= $this->createUser();
        $this->login($user);

        return $user;
    }

    protected function assertGuest(): void
    {
        $this->assertFalse($this->session()->isLoggedIn());
    }

    protected function assertAuthenticated(): void
    {
        $this->assertTrue($this->session()->isLoggedIn());
    }
}
