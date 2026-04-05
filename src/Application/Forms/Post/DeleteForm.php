<?php

declare(strict_types=1);

namespace App\Application\Forms\Post;

use App\Constants\Routes;
use App\Contracts\Http\RequestInterface;
use App\Support\Form;

final class DeleteForm extends Form
{
    public const ACTION_URL = Routes::POST_DELETE;

    public const ID = 'id';

    public function __construct(RequestInterface $request)
    {
        parent::__construct($request, [self::ID]);
    }

    public function id(): int
    {
        return (int) $this->value(self::ID);
    }

    public function validate(): ?string
    {
        $id = $this->value(self::ID);

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
