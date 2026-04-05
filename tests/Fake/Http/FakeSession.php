<?php

declare(strict_types=1);

namespace Tests\Fake\Http;

use App\Contracts\Http\SessionInterface;
use App\Domain\User\User;

final class FakeSession implements SessionInterface
{
    public function __construct(private array $data = [], private array $flash = [])
    {
    }

    public function init(): void
    {
    }

    public function set(string $key, mixed $value): void
    {
        $this->data[$key] = $value;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    public function remove(string $key): void
    {
        unset($this->data[$key]);
    }

    public function clear(): void
    {
        $this->data = [];
    }

    public function login(User $user): void
    {
        $this->data['user_id'] = $user->id();
    }

    public function logout(): void
    {
        $this->clear();
    }

    public function isLoggedIn(): bool
    {
        return $this->userId() !== null;
    }

    public function userId(): ?int
    {
        $id = $this->get('user_id');

        return is_int($id) ? $id : null;
    }

    public function flash(string $key, mixed $value): void
    {
        $this->flash[$key] = $value;
    }

    public function getFlash(string $key, mixed $default = null): mixed
    {
        if (!array_key_exists($key, $this->flash)) {
            return $default;
        }

        $value = $this->flash[$key];

        unset($this->flash[$key]);

        return $value;
    }

    public function flashError(string $message): void
    {
        $this->flash('error', $message);
    }

    public function error(): ?string
    {
        $value = $this->getFlash('error');

        return is_string($value) ? $value : null;
    }

    public function flashOld(array $data): void
    {
        $this->flash('old', $data);
    }

    public function old(): array
    {
        $value = $this->getFlash('old');

        return is_array($value) ? $value : [];
    }
}
