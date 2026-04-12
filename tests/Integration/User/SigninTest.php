<?php

declare(strict_types=1);

namespace Tests\Integration\User;

use PHPUnit\Framework\Attributes\CoversNothing;
use App\Application\Controllers\UserController;

#[CoversNothing]
final class SigninTest extends UserTestCase
{
    public function testSigninSuccess(): void
    {
        $this->assertGuest();

        $this->createUser();

        $response = $this->postSignin();

        $this->assertAuthenticated();
        $this->assertRedirect($response, self::HOME_URL);
    }

    public function testSigninWithoutToken(): void
    {
        $this->createUser();

        $response = $this->postSignin(['token' => null]);

        $this->assertGuest();
        $this->assertError(UserController::ERROR_CSRF);
        $this->assertRedirect($response, self::SIGNIN_URL);
    }

    public function testSigninInvalidToken(): void
    {
        $this->createUser();

        $response = $this->postSignin(['token' => 'invalid-token']);

        $this->assertGuest();
        $this->assertError(UserController::ERROR_CSRF);
        $this->assertRedirect($response, self::SIGNIN_URL);
    }

    public function testSigninWrongPassword(): void
    {
        $this->createUser();

        $response = $this->postSignin(['pass' => 'wrong-password']);

        $this->assertGuest();
        $this->assertError(UserController::ERROR_LOGIN);
        $this->assertRedirect($response, self::SIGNIN_URL);
    }

    public function testSigninUserNotFound(): void
    {
        $response = $this->postSignin();

        $this->assertGuest();
        $this->assertError(UserController::ERROR_LOGIN);
        $this->assertRedirect($response, self::SIGNIN_URL);
    }
}
