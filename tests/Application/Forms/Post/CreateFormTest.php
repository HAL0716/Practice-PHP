<?php

declare(strict_types=1);

namespace Tests\Application\Forms\Post;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use App\Application\Forms\Post\CreateForm;
use Tests\Fake\Infrastructure\Http\FakeRequest;

#[CoversClass(CreateForm::class)]
final class CreateFormTest extends TestCase
{
    public function testValidateSuccess(): void
    {
        $form = $this->createForm();

        $this->assertNull($form->validate());
        $this->assertSame('comment', $form->comment());
    }

    public function testValidateRequired(): void
    {
        $form = $this->createForm([ 'comment' => '' ]);

        $this->assertSame(CreateForm::ERROR_REQUIRED_FIELDS, $form->validate());
    }

    public function testNormalize(): void
    {
        $form = $this->createForm([ 'comment' => '  comment  ' ]);

        $this->assertSame('comment', $form->comment());
    }

    public function testOld(): void
    {
        $form = $this->createForm();

        $this->assertSame([
            'comment' => 'comment',
        ], $form->old());
    }

    private function createForm(array $overrides = []): CreateForm
    {
        $data = array_merge([
            'token' => 'token',
            'comment' => 'comment',
        ], $overrides);

        return new CreateForm(new FakeRequest(post: $data));
    }
}
