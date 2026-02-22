<?php

declare(strict_types=1);

/**
 * CSRF トークン管理
 */
final class CsrfToken
{
    private const TOKEN_LENGTH = 32;
    private const SESSION_KEY = 'token';
    private const POST_KEY = 'token';
    private const ERROR_MESSAGE = '不正なリクエストです';
    private const REDIRECT_URL = '/auth';

    public static function generate(): void
    {
        Session::start();
        if (empty(Session::get(self::SESSION_KEY))) {
            Session::set(
                self::SESSION_KEY,
                bin2hex(random_bytes(self::TOKEN_LENGTH))
            );
        }
    }

    public static function verify(): void
    {
        if (!self::isValid()) {
            Session::flash('error', self::ERROR_MESSAGE);
            header('Location: ' . self::REDIRECT_URL);
            exit;
        }
        self::consume();
    }

    private static function isValid(): bool
    {
        return Session::has(self::SESSION_KEY)
            && isset($_POST[self::POST_KEY])
            && hash_equals(
                Session::get(self::SESSION_KEY),
                $_POST[self::POST_KEY]
            );
    }

    private static function consume(): void
    {
        Session::remove(self::SESSION_KEY);
    }
}
