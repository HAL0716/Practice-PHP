<?php

declare(strict_types=1);

namespace App\Forms;

use App\Constants\Routes;
use App\Core\Form;

final class SignupForm extends Form
{
    public const actionURL = Routes::SIGNUP;

    public const NAME         = 'name';
    public const MAIL         = 'mail';
    public const PASS         = 'pass';
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
        return trim($this->data[self::NAME]);
    }

    public function mail(): string
    {
        return strtolower(trim($this->data[self::MAIL]));
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

        if (!$this->isMatch($this->pass(), $this->passConfirm())) {
            return self::ERROR_PASSWORD_MISMATCH;
        }

        return null;
    }

    public function old(array $except = []): array
    {
        return parent::old(array_merge($except, [
            self::PASS,
            self::PASS_CONFIRM,
        ]));
    }
}
