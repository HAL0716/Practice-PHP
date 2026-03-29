<?php

declare(strict_types=1);

namespace Tests\Core\Http;

use App\Core\Http\Controller;
use App\Contracts\Http\RequestInterface;
use App\Contracts\Http\SessionInterface;
use App\Contracts\Http\ResponseInterface;
use App\Contracts\Security\CsrfInterface;
use App\Core\Form;

final class TestController extends Controller
{
    public function __construct(RequestInterface $request, SessionInterface $session, ResponseInterface $response, CsrfInterface $csrf)
    {
        parent::__construct($request, $session, $response, $csrf);
    }

    public function dispatchTest(?callable $post = null, ?callable $get = null): void
    {
        $this->dispatch($post, $get);
    }

    public function requireLoginTest(): void
    {
        $this->requireLogin();
    }

    public function redirectTest(string $url, string $error = '', array $old = []): void
    {
        $this->redirect($url, $error, $old);
    }

    public function redirectSelfTest(string $error = '', array $old = []): void
    {
        $this->redirectSelf($error, $old);
    }

    public function checkCsrfTest(string $token): ?string
    {
        return $this->checkCsrf($token);
    }

    public function userIdTest(): int
    {
        return $this->userId();
    }

    public function ensureValidFormTest(Form $form, ?string $redirect = null): bool
    {
        return $this->ensureValidForm($form, $redirect);
    }
}
