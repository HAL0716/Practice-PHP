<?php

declare(strict_types=1);

require_once __DIR__ . '/../core/Form.php';

final class SigninForm extends Form
{
    public const TOKEN = 'token';
    public const MAIL  = 'mail';
    public const PASS  = 'pass';

    private const ERROR_INVALID_INPUT = 'メールアドレスとパスワードを入力してください';
    private const ERROR_INVALID_EMAIL = 'メールアドレスの形式が正しくありません';

    public function __construct()
    {
        parent::__construct([
            self::TOKEN,
            self::MAIL,
            self::PASS,
        ]);
    }

    public function token(): string
    {
        return $this->data[self::TOKEN];
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
        foreach ($this->data as $value) {
            if (empty($value)) {
                return self::ERROR_INVALID_INPUT;
            }
        }

        if (!filter_var($this->data[self::MAIL], FILTER_VALIDATE_EMAIL)) {
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
