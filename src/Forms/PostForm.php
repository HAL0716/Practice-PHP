<?php

declare(strict_types=1);

namespace App\Forms;

use App\Constants\Routes;
use App\Core\Form;

final class PostForm extends Form
{
    public const actionURL = Routes::HOME;

    public const COMMENT = 'comment';

    public function __construct()
    {
        parent::__construct([
            self::COMMENT,
        ]);
    }

    public function comment(): string
    {
        return trim($this->data[self::COMMENT]);
    }

    public function validate(): ?string
    {
        if ($this->hasEmpty([
            $this->comment(),
        ])) {
            return self::ERROR_REQUIRED_FIELDS;
        }

        return null;
    }

    public function old(array $except = []): array
    {
        return parent::old($except);
    }
}
