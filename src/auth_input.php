<?php

declare(strict_types=1);

final class AuthInput
{
    public string $name;
    public string $mail;
    public string $pass;
    public string $passConfirm;

    public function __construct()
    {
        $this->name = Request::post(FormFields::NAME, '');
        $this->mail = Request::post(FormFields::MAIL, '');
        $this->pass = Request::post(FormFields::PASS, '');
        $this->passConfirm = Request::post(FormFields::PASS_CONFIRM, '');
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
