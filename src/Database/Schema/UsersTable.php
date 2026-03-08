<?php

declare(strict_types=1);

final class UsersTable
{
    public const TABLE      = 'users';
    public const ALIAS      = 'u';

    public const ID         = 'id';
    public const USERNAME   = 'username';
    public const EMAIL      = 'email';
    public const PASSWORD   = 'password';
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';

    private function __construct()
    {
        throw new LogicException(
            "Cannot instantiate " . static::class
        );
    }
}
