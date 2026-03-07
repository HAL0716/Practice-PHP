<?php

declare(strict_types=1);

require_once __DIR__ . '/../core/Form.php';

final class SignupForm extends Form
{
    public const TOKEN        = 'token';
    public const NAME         = 'name';
    public const MAIL         = 'mail';
    public const PASS         = 'pass';
    public const PASS_CONFIRM = 'pass_confirm';

    private const ERROR_INVALID_INPUT     = 'すべての項目を入力してください';
    private const ERROR_PASSWORD_MISMATCH = 'パスワード確認が一致しません';

    public function __construct()
    {
        parent::__construct([
            self::TOKEN,
            self::NAME,
            self::MAIL,
            self::PASS,
            self::PASS_CONFIRM,
        ]);
    }

    public function token(): string
    {
        return $this->data[self::TOKEN];
    }

    public function name(): string
    {
        return $this->data[self::NAME];
    }

    public function mail(): string
    {
        return $this->data[self::MAIL];
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
        if ($this->name() === '' || $this->mail() === '' || $this->pass() === '' || $this->passConfirm() === '') {
            return self::ERROR_INVALID_INPUT;
        }

        if ($this->pass() !== $this->passConfirm()) {
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
