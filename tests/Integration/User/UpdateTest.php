<?php

declare(strict_types=1);

namespace Tests\Integration\User;

use PHPUnit\Framework\Attributes\CoversNothing;
use App\Application\Controllers\UserController;
use App\Application\Forms\User\UpdateForm;
use App\Domain\User\User;

#[CoversNothing]
final class UpdateTest extends UserTestCase
{
    public function testUpdateSuccess(): void
    {
        $this->loginAsUser();

        $response = $this->postUpdate([
            'name' => 'Updated User',
            'mail' => 'updated@example.com'
        ]);

        $this->assertAuthenticated();
        $this->assertRedirect($response, self::MYPAGE_URL);

        $updated = $this->users()->findByEmail('updated@example.com');
        $this->assertInstanceOf(User::class, $updated);
        $this->assertSame('Updated User', $updated->username());
    }

    public function testUpdateWithoutToken(): void
    {
        $this->loginAsUser();

        $response = $this->postUpdate([
            'token' => null
        ]);

        $this->assertError(UserController::ERROR_CSRF);
        $this->assertRedirect($response, self::MYPAGE_URL);
    }

    public function testUpdateInvalidToken(): void
    {
        $this->loginAsUser();

        $response = $this->postUpdate([
            'token' => 'invalid-token'
        ]);

        $this->assertError(UserController::ERROR_CSRF);
        $this->assertRedirect($response, self::MYPAGE_URL);
    }

    public function testUpdateWrongPassword(): void
    {
        $this->loginAsUser();

        $response = $this->postUpdate([
            'pass_current' => 'wrong-password'
        ]);

        $this->assertError(UserController::ERROR_PASSWORD);
        $this->assertRedirect($response, self::MYPAGE_URL);
    }

    public function testUpdateDuplicateEmail(): void
    {
        $this->loginAsUser();

        $this->createUser([
            'email' => 'other@example.com'
        ]);

        $response = $this->postUpdate([
            'mail' => 'other@example.com'
        ]);

        $this->assertError(UserController::ERROR_EXISTS);
        $this->assertRedirect($response, self::MYPAGE_URL);
    }

    public function testUpdateInvalidEmail(): void
    {
        $this->loginAsUser();

        $response = $this->postUpdate([
            'mail' => 'invalid-email'
        ]);

        $this->assertError(UpdateForm::ERROR_INVALID_EMAIL);
        $this->assertRedirect($response, self::MYPAGE_URL);
    }

    public function testUpdateEmptyName(): void
    {
        $this->loginAsUser();

        $response = $this->postUpdate([
            'name' => ''
        ]);

        $this->assertError(UpdateForm::ERROR_REQUIRED_FIELDS);
        $this->assertRedirect($response, self::MYPAGE_URL);
    }

    public function testUpdateAsGuest(): void
    {
        $this->assertGuest();

        $response = $this->postUpdate();

        $this->assertGuest();
        $this->assertRedirect($response, self::SIGNIN_URL);
    }
}
