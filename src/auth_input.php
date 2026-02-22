<?php

declare(strict_types=1);

final class AuthInput
{
    public const KEY_NAME = 'name';
    public const KEY_MAIL = 'mail';
    public const KEY_PASS = 'pass';
    public const KEY_PASS_CONFIRM = 'pass_confirm';

    public string $name;
    public string $mail;
    public string $pass;
    public string $passConfirm;

    public function __construct()
    {
        $this->name = Request::post(self::KEY_NAME, '');
        $this->mail = Request::post(self::KEY_MAIL, '');
        $this->pass = Request::post(self::KEY_PASS, '');
        $this->passConfirm = Request::post(self::KEY_PASS_CONFIRM, '');
    }

    /**
     * 入力バリデーション
     *
     * @param bool $isRegister サインインかログインか
     * @return bool
     */
    public function validate(bool $isRegister): bool
    {
        if ($isRegister && $this->name === '') {
            return false;
        }
        if ($isRegister && $this->pass !== $this->passConfirm) {
            return false;
        }
        return $this->mail !== '' && $this->pass !== '';
    }
}
