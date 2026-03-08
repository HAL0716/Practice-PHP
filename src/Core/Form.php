<?php

declare(strict_types=1);

namespace App\Core;

use App\Core\Http\Request;

abstract class Form
{
    public const TOKEN = 'token';

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

    public function old(array $except = []): array
    {
        return array_diff_key($this->data, array_flip($except));
    }
}
