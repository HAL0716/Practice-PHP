<?php

declare(strict_types=1);

final class Request
{
    private function __construct()
    {
        throw new LogicException(
            'Cannot instantiate ' . static::class
        );
    }

    public static function post(
        string $key,
        string $default = '',
        array $allowedValues = []
    ): string {
        return self::value($_POST, $key, $default, $allowedValues);
    }

    public static function query(
        string $key,
        string $default = '',
        array $allowedValues = []
    ): string {
        return self::value($_GET, $key, $default, $allowedValues);
    }

    public static function value(
        array $source,
        string $key,
        string $default = '',
        array $allowedValues = []
    ): string {
        if (!isset($source[$key]) || is_array($source[$key])) {
            return $default;
        }

        $value = trim((string)$source[$key]);

        if ($allowedValues !== [] && !in_array($value, $allowedValues, true)) {
            return $default;
        }

        return $value;
    }

    public static function array(
        array $source,
        string $key,
        array $default = []
    ): array {
        if (!isset($source[$key]) || !is_array($source[$key])) {
            return $default;
        }

        $value = $source[$key] ?? $default;

        if (!is_array($value)) {
            return $default;
        }

        return array_map(
            fn ($v) => is_string($v) ? trim($v) : $v,
            $value
        );
    }

    public static function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    public static function isGet(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }

    public static function path(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        return parse_url($uri, PHP_URL_PATH) ?: '/';
    }
}
