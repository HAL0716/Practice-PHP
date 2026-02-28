<?php

declare(strict_types=1);

final class Html
{
    public static function escape(
        ?string $string,
        int $flags = ENT_QUOTES,
        string $encoding = 'UTF-8',
        bool $double_encode = true
    ): void {
        echo htmlspecialchars($string ?? '', $flags, $encoding, $double_encode);
    }
}
