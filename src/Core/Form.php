<?php

declare(strict_types=1);

namespace App\Core;

use App\Core\Http\Request;

abstract class Form
{
    public const TOKEN = 'token';

    protected const ERROR_REQUIRED_FIELDS = '未入力の項目があります';
    protected const ERROR_INVALID_EMAIL = '不正なメールアドレスです';
    protected const ERROR_MISMATCH_PASSWORD = 'パスワードが一致しません';

    protected array $data = [];

    public function __construct(array $fields = [])
    {
        $this->data[self::TOKEN] = Request::post(self::TOKEN, '');
        foreach ($fields as $field) {
            $this->data[$field] = Request::post($field, '');
        }
    }

    public function token(): string
    {
        return $this->data[self::TOKEN];
    }

    protected function hasEmpty(array $values): bool
    {
        foreach ($values as $value) {
            if (trim((string)$value) === '') {
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
        return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    public function old(array $except = []): array
    {
        return array_diff_key($this->data, array_flip($except));
    }
}
