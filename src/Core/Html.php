<?php

declare(strict_types=1);

final class Html
{
    private function __construct()
    {
        throw new LogicException(
            'Cannot instantiate ' . static::class
        );
    }

    public static function escape(
        ?string $string,
        int $flags = ENT_QUOTES,
        string $encoding = 'UTF-8',
        bool $double_encode = true
    ): string {
        return htmlspecialchars($string ?? '', $flags, $encoding, $double_encode);
    }
}
