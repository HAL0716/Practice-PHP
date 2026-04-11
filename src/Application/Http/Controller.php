<?php

declare(strict_types=1);

namespace App\Application\Http;

use App\Application\Constants\RoutePaths;
use App\Application\Security\CsrfInterface;
use App\Infrastructure\Http\Response;
use App\Support\Form;

abstract class Controller
{
    public const ERROR_CSRF = '不正なリクエストです。再度お試しください。';
    public const ERROR_SYSTEM = 'システムエラーが発生しました。時間をおいて再度お試しください。';

    protected const LAYOUT = 'layouts/default';

    public function __construct(
        protected RequestInterface $request,
        protected SessionInterface $session,
        protected CsrfInterface $csrf
    ) {
    }

    final protected function dispatch(?callable $post = null, ?callable $get = null): ResponseInterface
    {
        if ($this->request->isPost() && $post) {
            return $post();
        }

        if ($this->request->isGet() && $get) {
            return $get();
        }

        return $this->redirectSelf(self::ERROR_SYSTEM);
    }

    protected function requireLogin(): ?ResponseInterface
    {
        if (!$this->session->isLoggedIn()) {
            return $this->redirect(RoutePaths::USER_SIGNIN);
        }

        return null;
    }

    final protected function redirect(string $url, string $error = '', array $old = []): ResponseInterface
    {
        if ($error !== '') {
            $this->session->flashError($error);
        }

        if ($old !== []) {
            $this->session->flashOld($old);
        }

        return Response::redirect($url);
    }

    final protected function redirectSelf(string $error = '', array $old = []): ResponseInterface
    {
        return $this->redirect($this->request->path(), $error, $old);
    }

    final protected function ensureValidForm(Form $form, ?string $redirect = null): ?ResponseInterface
    {
        $redirect ??= $this->request->path();

        $error = $this->checkCsrf($form->token()) ?? $form->validate();

        if ($error !== null) {
            return $this->redirect($redirect, $error, $form->old());
        }

        return null;
    }

    final protected function checkCsrf(string $token): ?string
    {
        return $this->csrf->verify($token) ? null : static::ERROR_CSRF;
    }

    final protected function render(string $view, array $data = [], bool $useLayout = true): ResponseInterface
    {
        extract($this->viewData($data), EXTR_SKIP);

        ob_start();
        require $this->viewFile($view);
        $content = (string) ob_get_clean();

        if ($useLayout) {
            ob_start();
            require $this->viewFile(static::LAYOUT);
            $content = (string) ob_get_clean();
        }

        return new Response(200, [], $content);
    }

    final protected function userId(): int|ResponseInterface
    {
        $id = $this->session->userId();

        if ($id === null) {
            return $this->redirect(RoutePaths::USER_SIGNIN);
        }

        return $id;
    }

    private function viewData(array $data): array
    {
        return $data + [
            'token' => $this->csrf->token(),
            'error' => $this->session->error(),
            'old' => $this->session->old(),
        ];
    }

    private function viewFile(string $view): string
    {
        $path = __DIR__ . '/../Views/' . $view . '.php';

        if (!is_file($path)) {
            throw new \RuntimeException("View not found: {$view}");
        }

        return $path;
    }
}
