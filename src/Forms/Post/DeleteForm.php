<?php

declare(strict_types=1);

namespace App\Forms\Post;

use App\Constants\Routes;
use App\Core\Form;

final class DeleteForm extends Form
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
        $id = $this->data[self::ID];

        if ($this->hasEmpty([
            $id
        ])) {
            return self::ERROR_REQUIRED_FIELDS;
        }

        if (!$this->isDigits($id)) {
            return self::ERROR_INVALID_NUMBER;
        }

        return null;
    }
}
