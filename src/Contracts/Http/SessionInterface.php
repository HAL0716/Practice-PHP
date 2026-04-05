<?php

declare(strict_types=1);

namespace App\Contracts\Http;

use App\Domain\User\User;

interface SessionInterface
{
    public function init(): void;

    public function set(string $key, mixed $value): void;

    public function get(string $key, mixed $default = null): mixed;

    public function remove(string $key): void;

    public function clear(): void;

    public function login(User $user): void;

    public function logout(): void;

    public function isLoggedIn(): bool;

    public function userId(): ?int;

    public function flash(string $key, mixed $value): void;

    public function getFlash(string $key, mixed $default = null): mixed;

    public function flashError(string $message): void;

    public function error(): ?string;

    public function flashOld(array $data): void;

    public function old(): array;
}
