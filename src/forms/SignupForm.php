<?php

declare(strict_types=1);

require_once __DIR__ . '/../core/Form.php';

final class SignupForm extends Form
{
    public const NAME         = 'name';
    public const MAIL         = 'mail';
    public const PASS         = 'pass';
    public const PASS_CONFIRM = 'pass_confirm';

    private const ERROR_INVALID_INPUT     = 'すべての項目を入力してください';
    private const ERROR_INVALID_EMAIL     = 'メールアドレスの形式が正しくありません';
    private const ERROR_PASSWORD_MISMATCH = 'パスワード確認が一致しません';

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
        if ($this->name() === '' || $this->mail() === '' || $this->pass() === '' || $this->passConfirm() === '') {
            return self::ERROR_INVALID_INPUT;
        }

        if (!filter_var($this->mail(), FILTER_VALIDATE_EMAIL)) {
            return self::ERROR_INVALID_EMAIL;
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
