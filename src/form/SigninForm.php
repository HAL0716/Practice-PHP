<?php

declare(strict_types=1);

require_once __DIR__ . '/../core/Form.php';

final class SigninForm extends Form
{
    public const MAIL  = 'mail';
    public const PASS  = 'pass';

    private const ERROR_INVALID_INPUT = 'メールアドレスとパスワードを入力してください';
    private const ERROR_INVALID_EMAIL = 'メールアドレスの形式が正しくありません';

    public function __construct()
    {
        parent::__construct([
            self::MAIL,
            self::PASS,
        ]);
    }

    public function mail(): string
    {
        return $this->data[self::MAIL];
    }

    public function pass(): string
    {
        return $this->data[self::PASS];
    }

    public function validate(): ?string
    {
        if ($this->mail() === '' || $this->pass() === '') {
            return self::ERROR_INVALID_INPUT;
        }

        if (!filter_var($this->mail(), FILTER_VALIDATE_EMAIL)) {
            return self::ERROR_INVALID_EMAIL;
        }

        return null;
    }

    public function old(array $except = []): array
    {
        return parent::old(array_merge($except, [
            self::PASS,
        ]));
    }
}
