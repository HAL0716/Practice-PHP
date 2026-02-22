<?php

declare(strict_types=1);

final class Html
{
    /**
     * エスケープ処理
     *
     * @param string|null $string
     * @param int $flags
     * @param string $encoding
     * @param boolean $double_encode
     * @return string
     */
    public static function escape(
        ?string $string,
        int $flags = ENT_QUOTES,
        string $encoding = 'UTF-8',
        bool $double_encode = true
    ): void {
        echo htmlspecialchars($string ?? '', $flags, $encoding, $double_encode);
    }
}
