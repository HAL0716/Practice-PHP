<?php

declare(strict_types=1);

namespace App\Forms;

use App\Constants\Routes;
use App\Core\Form;

final class MypageForm extends Form
{
    public const actionURL    = Routes::MYPAGE;

    public const NAME         = 'name';
    public const MAIL         = 'mail';
    public const PASS         = 'pass';
    public const PASS_CONFIRM = 'pass_confirm';
    public const PASS_CURRENT = 'pass_current';

    private const ERROR_PASSWORD_MISMATCH = '新しいパスワード確認が一致しません';
    private const ERROR_SAME_PASSWORD     = '現在のパスワードと新しいパスワードが同じです';

    public function __construct()
    {
        parent::__construct([
            self::NAME,
            self::MAIL,
            self::PASS,
            self::PASS_CONFIRM,
            self::PASS_CURRENT,
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

    public function passCurrent(): string
    {
        return $this->data[self::PASS_CURRENT];
    }

    public function validate(): ?string
    {
        if ($this->hasEmpty([
            $this->name(),
            $this->mail(),
            $this->passCurrent(),
        ])) {
            return self::ERROR_REQUIRED_FIELDS;
        }

        if (!$this->isValidEmail($this->mail())) {
            return self::ERROR_INVALID_EMAIL;
        }

        // パスワードは任意だが、入力された場合は確認と現在のパスワードも必須
        if ($this->pass() !== '') {
            if ($this->pass() === $this->passCurrent()) {
                return self::ERROR_SAME_PASSWORD;
            }

            if ($this->pass() !== $this->passConfirm()) {
                return self::ERROR_PASSWORD_MISMATCH;
            }
        }

        return null;
    }

    public function old(array $except = []): array
    {
        return parent::old(array_merge($except, [
            self::PASS,
            self::PASS_CONFIRM,
            self::PASS_CURRENT,
        ]));
    }
}
