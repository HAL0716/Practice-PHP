<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

use App\Application\Http\SessionInterface;
use App\Domain\User\User;

final class Session implements SessionInterface
{
    private const USER_ID = 'user_id';

    private const FLASH_PREFIX = '_flash_';
    private const FLASH_ERROR  = 'error';
    private const FLASH_OLD    = 'old';

    public function __construct()
    {
    }

    public function init(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    private function regenerate(bool $deleteOldSession = true): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id($deleteOldSession);
        }
    }

    public function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public function clear(): void
    {
        $_SESSION = [];
    }

    public function destroy(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    public function login(User $user): void
    {
        $this->regenerate();

        $_SESSION[self::USER_ID] = $user->id();
    }

    public function logout(): void
    {
        $this->clear();

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();

            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        $this->destroy();
    }

    public function isLoggedIn(): bool
    {
        return $this->userId() !== null;
    }

    public function userId(): ?int
    {
        $id = $this->get(self::USER_ID);

        return is_int($id) ? $id : null;
    }

    public function flash(string $key, mixed $value): void
    {
        $this->set(self::FLASH_PREFIX . $key, $value);
    }

    public function getFlash(string $key, mixed $default = null): mixed
    {
        $flashKey = self::FLASH_PREFIX . $key;

        $value = $this->get($flashKey, $default);

        $this->remove($flashKey);

        return $value;
    }

    public function flashError(string $message): void
    {
        $this->flash(self::FLASH_ERROR, $message);
    }

    public function error(): ?string
    {
        return $this->getFlash(self::FLASH_ERROR);
    }

    public function flashOld(array $data): void
    {
        $this->flash(self::FLASH_OLD, $data);
    }

    public function old(): array
    {
        return $this->getFlash(self::FLASH_OLD, []);
    }
}
