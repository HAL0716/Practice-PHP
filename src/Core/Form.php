<?php

declare(strict_types=1);

namespace App\Core;

use App\Core\Http\Request;

abstract class Form
{
    public const TOKEN = 'token';

    protected const ERROR_REQUIRED_FIELDS = '未入力の項目があります';

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

    public function old(array $except = []): array
    {
        return array_diff_key($this->data, array_flip($except));
    }
}
