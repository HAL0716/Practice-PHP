<?php

declare(strict_types=1);

namespace App\Core;

final class Html
{
    private function __construct()
    {
        throw new \LogicException(
            'Cannot instantiate ' . static::class
        );
    }

    public static function escape(mixed $value, int $flags = ENT_QUOTES, string $encoding = 'UTF-8', bool $double_encode = true): string
    {
        if (is_array($value)) {
            throw new \InvalidArgumentException('Array is not allowed');
        }

        return htmlspecialchars((string) ($value ?? ''), $flags, $encoding, $double_encode);
    }
}
