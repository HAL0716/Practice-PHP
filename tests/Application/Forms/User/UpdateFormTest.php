<?php

declare(strict_types=1);

namespace Tests\Application\Forms\User;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use App\Application\Forms\User\UpdateForm;
use Tests\Fake\Http\FakeRequest;

#[CoversClass(UpdateForm::class)]
final class UpdateFormTest extends TestCase
{
    public function testValidateSuccessWithoutPasswordChange(): void
    {
        $form = $this->createForm();

        $this->assertNull($form->validate());
    }

    public function testValidateSuccessWithPasswordChange(): void
    {
        $form = $this->createForm([
            'pass' => 'pass1234',
            'pass_confirm' => 'pass1234',
        ]);

        $this->assertNull($form->validate());
    }

    public function testValidateRequired(): void
    {
        $form = $this->createForm([
            'name' => '',
            'mail' => '',
            'pass_current' => '',
        ]);

        $this->assertSame(UpdateForm::ERROR_REQUIRED_FIELDS, $form->validate());
    }

    public function testValidateInvalidEmail(): void
    {
        $form = $this->createForm([
            'mail' => 'invalid',
        ]);

        $this->assertSame(UpdateForm::ERROR_INVALID_EMAIL, $form->validate());
    }

    public function testValidateInvalidPassword(): void
    {
        $form = $this->createForm([
            'pass' => 'short',
            'pass_confirm' => 'short',
        ]);

        $this->assertSame(UpdateForm::ERROR_INVALID_PASSWORD, $form->validate());
    }

    public function testValidatePasswordConfirmRequired(): void
    {
        $form = $this->createForm([
            'pass' => 'pass1234',
            'pass_confirm' => '',
        ]);

        $this->assertSame(UpdateForm::ERROR_REQUIRED_FIELDS, $form->validate());
    }

    public function testValidatePasswordMismatch(): void
    {
        $form = $this->createForm([
            'pass' => 'pass1234',
            'pass_confirm' => 'different',
        ]);

        $this->assertSame(UpdateForm::ERROR_PASSWORD_MISMATCH, $form->validate());
    }

    public function testOldReturnsOnlyAllowedFields(): void
    {
        $form = $this->createForm();

        $this->assertSame([
            'name' => 'name',
            'mail' => 'test@example.com',
        ], $form->old());
    }

    private function createForm(array $overrides = []): UpdateForm
    {
        $data = array_merge([
            'token' => 'token',
            'name' => 'name',
            'mail' => 'test@example.com',
            'pass' => '',
            'pass_confirm' => '',
            'pass_current' => 'current123',
        ], $overrides);

        return new UpdateForm(new FakeRequest(post: $data));
    }
}
