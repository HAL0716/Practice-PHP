<?php

declare(strict_types=1);

namespace Tests\Application\Http;

use App\Application\Http\Controller;
use App\Application\Http\RequestInterface;
use App\Application\Http\ResponseInterface;
use App\Application\Http\SessionInterface;
use App\Application\Security\CsrfInterface;

final class TestController extends Controller
{
    public function __construct(
        RequestInterface $request,
        SessionInterface $session,
        CsrfInterface $csrf
    ) {
        parent::__construct($request, $session, $csrf);
    }

    public function checkCsrfTest(string $token): ?string
    {
        return $this->checkCsrf($token);
    }

    public function userIdTest(): int|ResponseInterface
    {
        return $this->userId();
    }

    public function ensureValidFormTest($form, ?string $redirect = null)
    {
        return $this->ensureValidForm($form, $redirect);
    }
}
