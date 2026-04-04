<?php

declare(strict_types=1);

namespace App\Forms\User;

use App\Constants\Routes;
use App\Contracts\Http\RequestInterface;
use App\Core\Form;

final class SigninForm extends Form
{
    public const ACTION_URL = Routes::USER_SIGNIN;

    public const MAIL = 'mail';
    public const PASS = 'pass';

    public function __construct(RequestInterface $request)
    {
        parent::__construct($request, [self::MAIL,self::PASS]);
    }

    public function mail(): string
    {
        return $this->normalizedLower(self::MAIL);
    }

    public function pass(): string
    {
        return $this->data[self::PASS];
    }

    public function validate(): ?string
    {
        if ($this->hasEmpty([
            $this->mail(),
            $this->pass(),
        ])) {
            return self::ERROR_REQUIRED_FIELDS;
        }

        if (!$this->isValidEmail($this->mail())) {
            return self::ERROR_INVALID_EMAIL;
        }

        return null;
    }

    protected function oldFields(): array
    {
        return [
            self::MAIL,
        ];
    }
}
