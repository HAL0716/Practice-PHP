<?php

declare(strict_types=1);

namespace App\Forms;

use App\Constants\Routes;
use App\Core\Form;

final class SignupForm extends Form
{
    public const actionURL = Routes::SIGNUP;

    public const NAME = 'name';
    public const MAIL = 'mail';
    public const PASS = 'pass';
    public const PASS_CONFIRM = 'pass_confirm';

    public function __construct()
    {
        parent::__construct([
            self::NAME,
            self::MAIL,
            self::PASS,
            self::PASS_CONFIRM,
        ]);
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
        return $this->data[self::PASS];
    }

    public function passConfirm(): string
    {
        return $this->data[self::PASS_CONFIRM];
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
