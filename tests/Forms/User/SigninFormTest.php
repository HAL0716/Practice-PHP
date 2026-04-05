<?php

declare(strict_types=1);

namespace Tests\Forms\User;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use App\Forms\User\SigninForm;
use Tests\Fake\Http\FakeRequest;

#[CoversClass(SigninForm::class)]
final class SigninFormTest extends TestCase
{
    public function testValidateSuccess(): void
    {
        $form = $this->createForm();

        $this->assertNull($form->validate());
        $this->assertSame('test@example.com', $form->mail());
        $this->assertSame('pass1234', $form->pass());
    }

    public function testValidateRequired(): void
    {
        $form = $this->createForm([
            'mail' => '',
            'pass' => '',
        ]);

        $this->assertSame(SigninForm::ERROR_REQUIRED_FIELDS, $form->validate());
    }

    public function testValidateInvalidEmail(): void
    {
        $form = $this->createForm([
            'mail' => 'invalid',
        ]);

        $this->assertSame(SigninForm::ERROR_INVALID_EMAIL, $form->validate());
    }

    public function testOldReturnsOnlyMail(): void
    {
        $form = $this->createForm();

        $this->assertSame([
            'mail' => 'test@example.com',
        ], $form->old());
    }

    private function createForm(array $overrides = []): SigninForm
    {
        $data = array_merge([
            'token' => 'token',
            'mail' => 'test@example.com',
            'pass' => 'pass1234',
        ], $overrides);

        return new SigninForm(new FakeRequest(post: $data));
    }
}
