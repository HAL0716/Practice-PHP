<?php

declare(strict_types=1);

namespace App\Core;

use App\Contracts\Http\RequestInterface;

abstract class Form
{
    private const PASSWORD_LENGTH = 8;

    public const TOKEN = 'token';

    public const ERROR_REQUIRED_FIELDS   = '未入力の項目があります';
    public const ERROR_INVALID_EMAIL     = '不正なメールアドレスです';
    public const ERROR_INVALID_PASSWORD  = 'パスワードは英字と数字を含む8文字以上である必要があります';
    public const ERROR_PASSWORD_MISMATCH = 'パスワードが一致しません';
    public const ERROR_INVALID_NUMBER    = '数字でなければなりません';

    private array $data = [];

    public function __construct(RequestInterface $request, array $fields = [])
    {
        $this->data[self::TOKEN] = $request->post(self::TOKEN);

        foreach ($fields as $field) {
            $this->data[$field] = $request->post($field);
        }
    }

    protected function value(string $key): string
    {
        return $this->data[$key] ?? '';
    }

    public function token(): string
    {
        return $this->value(self::TOKEN);
    }

    protected function normalized(string $key): string
    {
        return trim($this->value($key));
    }

    protected function normalizedLower(string $key): string
    {
        return strtolower($this->normalized($key));
    }

    protected function isEmpty(string $value): bool
    {
        return trim($value) === '';
    }

    protected function hasEmpty(array $values): bool
    {
        foreach ($values as $value) {
            if ($this->isEmpty((string) $value)) {
                return true;
            }
        }

        return false;
    }

    protected function isMatch(string $value1, string $value2): bool
    {
        return $value1 === $value2;
    }

    protected function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    protected function isValidPassword(string $password): bool
    {
        return strlen($password) >= self::PASSWORD_LENGTH
            && preg_match('/[A-Za-z]/', $password) === 1
            && preg_match('/[0-9]/', $password) === 1;
    }

    protected function isDigits(string $value): bool
    {
        return ctype_digit($value);
    }

    abstract public function validate(): ?string;

    public function old(): array
    {
        $old = [];

        foreach ($this->oldFields() as $field) {
            $old[$field] = $this->value($field);
        }

        return $old;
    }

    protected function oldFields(): array
    {
        return [];
    }
}
