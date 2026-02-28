<?php

declare(strict_types=1);

class SessionKeys
{
    public const CSRF_TOKEN = 'csrf_token';
    public const CSRF_TOKEN_TIME = 'csrf_token_time';

    public const USER_ID   = 'user_id';
    public const USER_NAME = 'user_name';

    public const ERRORS = 'errors';
    public const OLD = 'old';

    public const LOGIN_ATTEMPTS = 'login_attempts';
    public const LOGIN_ATTEMPT_TIME = 'login_attempt_time';
}
