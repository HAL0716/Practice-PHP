<?php

declare(strict_types=1);

namespace App\Forms;

use App\Core\Form;

final class DeleteForm extends Form
{
    public const PASS = 'pass';

    private const ERROR_INVALID_INPUT     = 'パスワードを入力してください';

    public function __construct()
    {
        parent::__construct([
            self::PASS,
        ]);
    }

    public function pass(): string
    {
        return $this->data[self::PASS];
    }

    public function validate(): ?string
    {
        if ($this->pass() === '') {
            return self::ERROR_INVALID_INPUT;
        }

        return null;
    }
}
