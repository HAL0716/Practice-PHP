<?php

declare(strict_types=1);

namespace App\Forms;

use App\Constants\Routes;
use App\Core\Form;

final class DeletePostForm extends Form
{
    public const ACTION_URL = Routes::DELETE_POST;

    public const ID = 'id';

    public function __construct()
    {
        parent::__construct([
            self::ID,
        ]);
    }

    public function id(): int
    {
        return (int) $this->data[self::ID];
    }

    public function validate(): ?string
    {
        if ($this->hasEmpty([
            $this->id(),
        ])) {
            return self::ERROR_REQUIRED_FIELDS;
        }

        if (!$this->isDigits($this->data[self::ID])) {
            return self::ERROR_INVALID_NUMBER;
        }

        return null;
    }
}
