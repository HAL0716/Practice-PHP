<?php

declare(strict_types=1);

namespace App\Application\Forms\User;

use App\Application\Constants\RoutePaths;
use App\Application\Http\RequestInterface;
use App\Support\Form;

final class DeleteForm extends Form
{
    public const ACTION_URL = RoutePaths::USER_DELETE;

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
