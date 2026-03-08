<?php

declare(strict_types=1);

namespace App\Database\Schema;

final class PostsTable
{
    public const TABLE      = 'posts';
    public const ALIAS      = 'p';

    public const ID         = 'id';
    public const USER_ID    = 'user_id';
    public const COMMENT    = 'comment';
    public const CREATED_AT = 'created_at';

    private function __construct()
    {
        throw new \LogicException(
            "Cannot instantiate " . static::class
        );
    }
}
