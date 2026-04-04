<?php

declare(strict_types=1);

namespace Tests\Core;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use App\Core\Form;
use Tests\Fake\Form\FakeForm;
use Tests\Fake\Http\FakeRequest;

#[CoversClass(Form::class)]
final class FormTest extends TestCase
{
    public function testToken(): void
    {
        $form = $this->createForm(['token' => 'abc']);

        $this->assertSame('abc', $form->token());
    }

    public function testValue(): void
    {
        $form = $this->createForm(['name' => 'john']);

        $this->assertSame('john', $form->valueTest('name'));
        $this->assertSame('', $form->valueTest('unknown'));
    }

    public function testNormalized(): void
    {
        $form = $this->createForm(['name' => '  john  ']);

        $this->assertSame('john', $form->normalizedTest('name'));
    }

    public function testNormalizedLower(): void
    {
        $form = $this->createForm(['email' => '  TEST@EXAMPLE.COM ']);

        $this->assertSame('test@example.com', $form->normalizedLowerTest('email'));
    }

    public function testIsEmpty(): void
    {
        $form = $this->createForm();

        $this->assertTrue($form->isEmptyTest(''));
        $this->assertTrue($form->isEmptyTest('   '));
        $this->assertFalse($form->isEmptyTest('a'));
    }

    public function testHasEmpty(): void
    {
        $form = $this->createForm();

        $this->assertTrue($form->hasEmptyTest(['a', '']));
        $this->assertFalse($form->hasEmptyTest(['a', 'b']));
    }

    public function testIsMatch(): void
    {
        $form = $this->createForm();

        $this->assertTrue($form->isMatchTest('a', 'a'));
        $this->assertFalse($form->isMatchTest('a', 'b'));
    }

    public function testIsValidEmail(): void
    {
        $form = $this->createForm();

        $this->assertTrue($form->isValidEmailTest('test@example.com'));
        $this->assertFalse($form->isValidEmailTest('invalid'));
    }

    public function testIsValidPassword(): void
    {
        $form = $this->createForm();

        $this->assertTrue($form->isValidPasswordTest('abc12345'));
        $this->assertFalse($form->isValidPasswordTest('short'));
        $this->assertFalse($form->isValidPasswordTest('abcdefgh'));
        $this->assertFalse($form->isValidPasswordTest('12345678'));
    }

    public function testIsDigits(): void
    {
        $form = $this->createForm();

        $this->assertTrue($form->isDigitsTest('123'));
        $this->assertFalse($form->isDigitsTest('12a'));
    }

    public function testOld(): void
    {
        $form = $this->createForm([
            'name' => 'john',
            'email' => 'test@example.com',
            'ignored' => 'x'
        ]);

        $this->assertSame([
            'name' => 'john',
            'email' => 'test@example.com'
        ], $form->old());
    }

    private function createForm(array $post = []): FakeForm
    {
        return new FakeForm(new FakeRequest(post: $post), array_keys($post));
    }
}
