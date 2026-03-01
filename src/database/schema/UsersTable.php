<?php

declare(strict_types=1);

final class UsersTable
{
    public const TABLE    = 'users';
    public const ALIAS    = 'u';

    public const ID       = 'id';
    public const USERNAME = 'username';
    public const EMAIL    = 'email';
    public const PASSWORD = 'password';

    private function __construct()
    {
    }
}
