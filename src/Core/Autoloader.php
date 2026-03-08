<?php

declare(strict_types=1);

namespace App\Core;

final class Autoloader
{
    private const NAMESPACE_PREFIX = 'App\\';
    private const BASE_DIR = __DIR__ . '/../';

    private function __construct()
    {
        throw new \LogicException(
            'Cannot instantiate ' . static::class
        );
    }

    public static function register(): void
    {
        spl_autoload_register(function (string $class): void {

            if (!str_starts_with($class, self::NAMESPACE_PREFIX)) {
                return;
            }

            $relative = substr($class, strlen(self::NAMESPACE_PREFIX));

            $file = self::BASE_DIR . str_replace('\\', '/', $relative) . '.php';

            if (is_file($file)) {
                require $file;
            }
        });
    }
}
