<?php

declare(strict_types=1);

final class PostEntity
{
    public function __construct(
        private int $id,
        private ?int $userId,
        private string $comment,
        private ?string $username
    ) {
    }

    public function id(): int
    {
        return $this->id;
    }

    public function userId(): ?int
    {
        return $this->userId;
    }

    public function comment(): string
    {
        return $this->comment;
    }

    public function username(): ?string
    {
        return $this->username;
    }
}
