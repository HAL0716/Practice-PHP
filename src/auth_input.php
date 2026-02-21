<?php

declare(strict_types=1);

final class AuthInput
{
    public const KEY_NAME = 'name';
    public const KEY_MAIL = 'mail';
    public const KEY_PASS = 'pass';

    public string $name;
    public string $mail;
    public string $pass;

    public function __construct(array $data)
    {
        $this->name = trim(getStr($data, self::KEY_NAME, ''));
        $this->mail = trim(getStr($data, self::KEY_MAIL, ''));
        $this->pass = getStr($data, self::KEY_PASS, '');
    }

    public function validate(bool $isRegister): bool
    {
        if ($isRegister && $this->name === '') {
            return false;
        }
        return $this->mail !== '' && $this->pass !== '';
    }
}
