<?php

declare(strict_types=1);

abstract class Form
{
    protected array $data = [];

    public function __construct(array $fields = [])
    {
        foreach ($fields as $field) {
            $this->data[$field] = Request::post($field, '');
        }
    }

    public function old(array $except = []): array
    {
        return array_diff_key($this->data, array_flip($except));
    }
}
