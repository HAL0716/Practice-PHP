<?php

declare(strict_types=1);

function e(
    string $string,
    int $flags = ENT_QUOTES,
    ?string $encoding = 'UTF-8',
    bool $double_encode = true
): string {
    return htmlspecialchars($string, $flags, $encoding, $double_encode);
}
