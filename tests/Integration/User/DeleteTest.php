<?php

declare(strict_types=1);

namespace Tests\Integration\User;

use App\Application\Controllers\UserController;
use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
final class DeleteTest extends UserTestCase
{

    public function testDeleteSuccess(): void
    {
        $this->createUser();
        $this->login($this->users()->findByEmail(self::DEFAULT_EMAIL));

        $response = $this->postDelete();

        $this->assertGuest();
        $this->assertRedirect($response, self::SIGNIN_URL);

        $this->assertUserDeleted(self::DEFAULT_EMAIL);
    }

    public function testDeleteWithoutToken(): void
    {
        $this->createUser();
        $this->login($this->users()->findByEmail(self::DEFAULT_EMAIL));

        $response = $this->postDelete([
            'token' => null
        ]);

        $this->assertAuthenticated();
        $this->assertError(UserController::ERROR_CSRF);
        $this->assertRedirect($response, self::MYPAGE_URL);

        $this->assertUserExists(self::DEFAULT_EMAIL);
    }

    public function testDeleteInvalidToken(): void
    {
        $this->createUser();
        $this->login($this->users()->findByEmail(self::DEFAULT_EMAIL));

        $response = $this->postDelete([
            'token' => 'invalid-token'
        ]);

        $this->assertAuthenticated();
        $this->assertError(UserController::ERROR_CSRF);
        $this->assertRedirect($response, self::MYPAGE_URL);

        $this->assertUserExists(self::DEFAULT_EMAIL);
    }

    public function testDeleteWrongPassword(): void
    {
        $this->createUser();
        $this->login($this->users()->findByEmail(self::DEFAULT_EMAIL));

        $response = $this->postDelete([
            'pass_current' => 'wrong-password',
        ]);

        $this->assertAuthenticated();
        $this->assertError(UserController::ERROR_PASSWORD);
        $this->assertRedirect($response, self::MYPAGE_URL);

        $this->assertUserExists(self::DEFAULT_EMAIL);
    }

    public function testDeleteAsGuest(): void
    {
        $this->assertGuest();

        $response = $this->postDelete();

        $this->assertGuest();
        $this->assertRedirect($response, self::SIGNIN_URL);
    }

    // =========================
    // DB Assertions
    // =========================

    private function assertUserDeleted(string $email): void
    {
        $this->assertNull($this->users()->findByEmail($email));
    }

    private function assertUserExists(string $email): void
    {
        $this->assertNotNull($this->users()->findByEmail($email));
    }

    // =========================
    // Request Helper
    // =========================

    private function postDelete(array $override = [])
    {
        return $this->getResponse(
            'POST',
            self::DELETE_URL,
            array_merge([
                'token' => $this->csrfToken(),
                'pass_current' => self::DEFAULT_PASSWORD,
            ], $override)
        );
    }
}
