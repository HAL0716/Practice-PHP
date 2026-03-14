<?php

declare(strict_types=1);

namespace App\Forms;

use App\Constants\Routes;
use App\Core\Form;

final class DeleteForm extends Form
{
    public const ACTION_URL = Routes::DELETE;

    public const PASS = 'pass';

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
        if ($this->hasEmpty([
            $this->pass(),
        ])) {
            return self::ERROR_REQUIRED_FIELDS;
        }

        return null;
    }
}
