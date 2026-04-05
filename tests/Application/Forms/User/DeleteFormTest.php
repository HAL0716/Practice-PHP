<?php

declare(strict_types=1);

namespace Tests\Application\Forms\User;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use App\Application\Forms\User\DeleteForm;
use Tests\Fake\Http\FakeRequest;

#[CoversClass(DeleteForm::class)]
final class DeleteFormTest extends TestCase
{
    public function testValidateSuccess(): void
    {
        $form = $this->createForm();

        $this->assertNull($form->validate());
        $this->assertSame('current123', $form->passCurrent());
    }

    public function testValidateRequired(): void
    {
        $form = $this->createForm([
            'pass_current' => '',
        ]);

        $this->assertSame(DeleteForm::ERROR_REQUIRED_FIELDS, $form->validate());
    }

    public function testOldReturnsEmptyArray(): void
    {
        $form = $this->createForm();

        $this->assertSame([], $form->old());
    }

    private function createForm(array $overrides = []): DeleteForm
    {
        $data = array_merge([
            'token' => 'token',
            'pass_current' => 'current123',
        ], $overrides);

        return new DeleteForm(new FakeRequest(post: $data));
    }
}
