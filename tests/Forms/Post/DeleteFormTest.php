<?php

declare(strict_types=1);

namespace Tests\Forms\Post;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use App\Forms\Post\DeleteForm;
use Tests\Fake\Http\FakeRequest;

#[CoversClass(DeleteForm::class)]
final class DeleteFormTest extends TestCase
{
    public function testValidateSuccess(): void
    {
        $form = $this->createForm();

        $this->assertNull($form->validate());
        $this->assertSame(1, $form->id());
    }

    public function testValidateRequired(): void
    {
        $form = $this->createForm([ 'id' => '' ]);

        $this->assertSame(DeleteForm::ERROR_REQUIRED_FIELDS, $form->validate());
    }

    public function testValidateInvalidNumber(): void
    {
        $form = $this->createForm([ 'id' => 'abc' ]);

        $this->assertSame(DeleteForm::ERROR_INVALID_NUMBER, $form->validate());
    }

    public function testIdCastsToInt(): void
    {
        $form = $this->createForm([ 'id' => '10' ]);

        $this->assertSame(10, $form->id());
    }

    private function createForm(array $overrides = []): DeleteForm
    {
        $data = array_merge([
            'token' => 'token',
            'id' => '1',
        ], $overrides);

        return new DeleteForm(new FakeRequest(post: $data));
    }
}
