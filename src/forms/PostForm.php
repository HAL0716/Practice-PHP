<?php

declare(strict_types=1);

require_once __DIR__ . '/../core/Form.php';

final class PostForm extends Form
{
    public const COMMENT = 'comment';

    private const ERROR_COMMENT_REQUIRED = 'コメントは必須です';

    public function __construct()
    {
        parent::__construct([
            self::COMMENT,
        ]);
    }

    public function comment(): string
    {
        return $this->data[self::COMMENT];
    }

    public function validate(): ?string
    {
        if ($this->comment() === '') {
            return self::ERROR_COMMENT_REQUIRED;
        }

        return null;
    }

    public function old(array $except = []): array
    {
        return parent::old($except);
    }
}
