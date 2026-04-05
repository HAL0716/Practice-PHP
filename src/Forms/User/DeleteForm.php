<?php

declare(strict_types=1);

namespace App\Forms\User;

use App\Constants\Routes;
use App\Contracts\Http\RequestInterface;
use App\Core\Form;

final class DeleteForm extends Form
{
    public const ACTION_URL = Routes::USER_DELETE;

    public const PASS_CURRENT = 'pass_current';

    public function __construct(RequestInterface $request)
    {
        parent::__construct($request, [self::PASS_CURRENT]);
    }

    public function passCurrent(): string
    {
        return $this->value(self::PASS_CURRENT);
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
