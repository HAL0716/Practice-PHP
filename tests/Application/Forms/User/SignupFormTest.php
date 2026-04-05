<?php

declare(strict_types=1);

namespace Tests\Application\Forms\User;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use App\Application\Forms\User\SignupForm;
use Tests\Fake\Infrastructure\Http\FakeRequest;

#[CoversClass(SignupForm::class)]
final class SignupFormTest extends TestCase
{
    public function testValidateSuccess(): void
    {
        $form = $this->createForm();

        $this->assertNull($form->validate());
        $this->assertSame('name', $form->name());
        $this->assertSame('test@example.com', $form->mail());
    }

    public function testValidateRequired(): void
    {
        $form = $this->createForm([
            'name' => '',
            'mail' => '',
            'pass' => '',
            'pass_confirm' => '',
        ]);

        $this->assertSame(SignupForm::ERROR_REQUIRED_FIELDS, $form->validate());
    }

    public function testValidateInvalidEmail(): void
    {
        $form = $this->createForm([
            'mail' => 'invalid-email',
        ]);

        $this->assertSame(SignupForm::ERROR_INVALID_EMAIL, $form->validate());
    }

    public function testValidateInvalidPassword(): void
    {
        $form = $this->createForm([
            'pass' => 'short',
            'pass_confirm' => 'short',
        ]);

        $this->assertSame(SignupForm::ERROR_INVALID_PASSWORD, $form->validate());
    }

    public function testValidatePasswordMismatch(): void
    {
        $form = $this->createForm([
            'pass_confirm' => 'different',
        ]);

        $this->assertSame(SignupForm::ERROR_PASSWORD_MISMATCH, $form->validate());
    }

    public function testOldReturnsOnlyAllowedFields(): void
    {
        $form = $this->createForm();

        $this->assertSame([
            'name' => 'name',
            'mail' => 'test@example.com',
        ], $form->old());
    }

    private function createForm(array $overrides = []): SignupForm
    {
        $data = array_merge([
            'token' => 'token',
            'name' => 'name',
            'mail' => 'test@example.com',
            'pass' => 'pass1234',
            'pass_confirm' => 'pass1234',
        ], $overrides);

        return new SignupForm(new FakeRequest(post: $data));
    }
}
