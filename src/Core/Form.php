<?php

declare(strict_types=1);

namespace App\Core;

abstract class Form
{
    public const TOKEN = 'token';

    protected array $data = [];

    public function __construct(array $fields = [])
    {
        $this->data[self::TOKEN] = \App\Core\Request::post(self::TOKEN, '');
        foreach ($fields as $field) {
            $this->data[$field] = \App\Core\Request::post($field, '');
        }
    }

    public function token(): string
    {
        return $this->data[self::TOKEN];
    }

    public function old(array $except = []): array
    {
        return array_diff_key($this->data, array_flip($except));
    }
}
