<?php

declare(strict_types=1);

namespace Tests\Integration\User;

use PHPUnit\Framework\Attributes\CoversNothing;
use App\Application\Controllers\UserController;
use App\Application\Forms\User\SignupForm;
use App\Application\Http\ResponseInterface;
use App\Domain\User\User;

#[CoversNothing]
final class SignupTest extends UserTestCase
{
    public function testSignupSuccess(): void
    {
        $this->assertGuest();

        $response = $this->postSignup();

        $this->assertAuthenticated();
        $this->assertRedirect($response, self::HOME_URL);
        $this->assertUserCreated(self::DEFAULT_EMAIL);
    }

    public function testSignupWithoutToken(): void
    {
        $response = $this->postSignup(['token' => null]);

        $this->assertGuest();
        $this->assertError(UserController::ERROR_CSRF);
        $this->assertRedirect($response, self::SIGNUP_URL);
        $this->assertUserNotCreated(self::DEFAULT_EMAIL);
    }

    public function testSignupInvalidToken(): void
    {
        $response = $this->postSignup(['token' => 'invalid-token']);

        $this->assertGuest();
        $this->assertError(UserController::ERROR_CSRF);
        $this->assertRedirect($response, self::SIGNUP_URL);
        $this->assertUserNotCreated(self::DEFAULT_EMAIL);
    }

    public function testSignupPasswordMismatch(): void
    {
        $response = $this->postSignup(['pass_confirm' => 'different']);

        $this->assertGuest();
        $this->assertError(SignupForm::ERROR_PASSWORD_MISMATCH);
        $this->assertRedirect($response, self::SIGNUP_URL);
        $this->assertUserNotCreated(self::DEFAULT_EMAIL);
    }

    public function testSignupDuplicateEmail(): void
    {
        $this->users()->create('Test User', self::DEFAULT_EMAIL, 'password123');

        $response = $this->postSignup();

        $this->assertGuest();
        $this->assertError(UserController::ERROR_EXISTS);
        $this->assertRedirect($response, self::SIGNUP_URL);

        // 既存ユーザーが壊れていないことを保証
        $this->assertUserCreated(self::DEFAULT_EMAIL);
    }

    public function testSignupInvalidEmail(): void
    {
        $response = $this->postSignup(['mail' => 'invalid-email']);

        $this->assertGuest();
        $this->assertError(SignupForm::ERROR_INVALID_EMAIL);
        $this->assertRedirect($response, self::SIGNUP_URL);
        $this->assertUserNotCreated('invalid-email');
    }

    public function testSignupEmptyName(): void
    {
        $response = $this->postSignup(['name' => '']);

        $this->assertGuest();
        $this->assertError(SignupForm::ERROR_REQUIRED_FIELDS);
        $this->assertRedirect($response, self::SIGNUP_URL);
        $this->assertUserNotCreated(self::DEFAULT_EMAIL);
    }

    // =========================
    // DB Assertions
    // =========================

    private function assertUserCreated(string $email): void
    {
        $user = $this->users()->findByEmail($email);
        $this->assertInstanceOf(User::class, $user);
        $this->assertSame($email, $user->email());
    }

    private function assertUserNotCreated(string $email): void
    {
        $this->assertNull($this->users()->findByEmail($email));
    }

    // =========================
    // Request Helper
    // =========================

    private function postSignup(array $override = []): ResponseInterface
    {
        return $this->getResponse(
            'POST',
            self::SIGNUP_URL,
            array_merge([
                'token' => $this->csrfToken(),
                'name' => 'Test User',
                'mail' => self::DEFAULT_EMAIL,
                'pass' => 'password123',
                'pass_confirm' => 'password123',
            ], $override)
        );
    }
}
