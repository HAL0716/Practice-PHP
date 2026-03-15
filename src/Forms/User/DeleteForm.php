<?php

declare(strict_types=1);

namespace App\Forms\User;

use App\Constants\Routes;
use App\Core\Form;

final class DeleteForm extends Form
{
    public const ACTION_URL = Routes::DELETE;

    public const PASS_CURRENT = 'pass_current';

    public function __construct()
    {
        parent::__construct([
            self::PASS_CURRENT,
        ]);
    }

    public function passCurrent(): string
    {
        return $this->data[self::PASS_CURRENT];
    }

    public function validate(): ?string
    {
        if ($this->hasEmpty([
            $this->passCurrent(),
        ])) {
            return self::ERROR_REQUIRED_FIELDS;
        }

        return null;
    }
}
