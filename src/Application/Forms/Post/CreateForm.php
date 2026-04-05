<?php

declare(strict_types=1);

namespace App\Application\Forms\Post;

use App\Constants\Routes;
use App\Application\Http\RequestInterface;
use App\Support\Form;

final class CreateForm extends Form
{
    public const ACTION_URL = Routes::POST_HOME;

    public const COMMENT = 'comment';

    public function __construct(RequestInterface $request)
    {
        parent::__construct($request, [self::COMMENT]);
    }

    public function comment(): string
    {
        return $this->normalized(self::COMMENT);
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

    protected function oldFields(): array
    {
        return [
            self::COMMENT,
        ];
    }
}
