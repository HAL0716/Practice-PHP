<?php

declare(strict_types=1);

namespace App\Application\Forms\User;

use App\Constants\Routes;
use App\Contracts\Http\RequestInterface;
use App\Support\Form;

final class SignupForm extends Form
{
    public const ACTION_URL = Routes::USER_SIGNUP;

    public const NAME = 'name';
    public const MAIL = 'mail';
    public const PASS = 'pass';
    public const PASS_CONFIRM = 'pass_confirm';

    public function __construct(RequestInterface $request)
    {
        parent::__construct($request, [self::NAME,self::MAIL,self::PASS,self::PASS_CONFIRM]);
    }

    public function name(): string
    {
        return $this->normalized(self::NAME);
    }

    public function mail(): string
    {
        return $this->normalizedLower(self::MAIL);
    }

    public function pass(): string
    {
        return $this->value(self::PASS);
    }

    public function passConfirm(): string
    {
        return $this->value(self::PASS_CONFIRM);
    }

    public function validate(): ?string
    {
        if ($this->hasEmpty([
            $this->name(),
            $this->mail(),
            $this->pass(),
            $this->passConfirm(),
        ])) {
            return self::ERROR_REQUIRED_FIELDS;
        }

        if (!$this->isValidEmail($this->mail())) {
            return self::ERROR_INVALID_EMAIL;
        }

        if (!$this->isValidPassword($this->pass())) {
            return self::ERROR_INVALID_PASSWORD;
        }

        if (!$this->isMatch($this->pass(), $this->passConfirm())) {
            return self::ERROR_PASSWORD_MISMATCH;
        }

        return null;
    }

    protected function oldFields(): array
    {
        return [
            self::NAME,
            self::MAIL,
        ];
    }
}
