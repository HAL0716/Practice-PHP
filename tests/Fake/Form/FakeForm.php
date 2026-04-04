<?php

declare(strict_types=1);

namespace Tests\Fake\Form;

use App\Core\Form;
use Tests\Fake\Http\FakeRequest;

final class FakeForm extends Form
{
    public function __construct(FakeRequest $request, array $fields = [], private bool $valid = true)
    {
        parent::__construct($request, $fields);
    }

    public function validate(): ?string
    {
        return $this->valid ? null : 'Invalid form';
    }

    protected function oldFields(): array
    {
        return ['name', 'email'];
    }

    public function valueTest(string $key): string
    {
        return $this->value($key);
    }

    public function normalizedTest(string $key): string
    {
        return $this->normalized($key);
    }

    public function normalizedLowerTest(string $key): string
    {
        return $this->normalizedLower($key);
    }

    public function isEmptyTest(string $value): bool
    {
        return $this->isEmpty($value);
    }

    public function hasEmptyTest(array $values): bool
    {
        return $this->hasEmpty($values);
    }

    public function isMatchTest(string $a, string $b): bool
    {
        return $this->isMatch($a, $b);
    }

    public function isValidEmailTest(string $email): bool
    {
        return $this->isValidEmail($email);
    }

    public function isValidPasswordTest(string $password): bool
    {
        return $this->isValidPassword($password);
    }

    public function isDigitsTest(string $value): bool
    {
        return $this->isDigits($value);
    }
}
