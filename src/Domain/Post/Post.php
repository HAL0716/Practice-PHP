<?php

declare(strict_types=1);

namespace App\Domain\Post;

final class Post
{
    public function __construct(
        private int $id,
        private ?int $userId,
        private string $comment,
        private string $createdAt,
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

    public function createdAt(): string
    {
        return $this->createdAt;
    }

    public function createdAtJst(): string
    {
        $date = new \DateTime($this->createdAt, new \DateTimeZone('UTC'));

        return $date
            ->setTimezone(new \DateTimeZone('Asia/Tokyo'))
            ->format('Y-m-d H:i');
    }

    public function username(): ?string
    {
        return $this->username;
    }
}
