<?php

declare(strict_types=1);

namespace App\Application\Http;

use App\Application\Security\CsrfInterface;
use App\Constants\Routes;
use App\Support\Form;

abstract class Controller
{
    public const ERROR_CSRF = '不正なリクエストです。再度お試しください。';
    public const ERROR_SYSTEM = 'システムエラーが発生しました。時間をおいて再度お試しください。';

    protected const LAYOUT = 'layouts/default';

    public function __construct(protected RequestInterface $request, protected SessionInterface $session, protected ResponseInterface $response, protected CsrfInterface $csrf)
    {
    }

    final protected function dispatch(?callable $post = null, ?callable $get = null): void
    {
        if ($this->request->isPost() && $post) {
            $post();
            return;
        }

        if ($this->request->isGet() && $get) {
            $get();
            return;
        }

        $this->redirectSelf(self::ERROR_SYSTEM);
    }

    protected function requireLogin(): void
    {
        if (!$this->session->isLoggedIn()) {
            $this->redirect(Routes::USER_SIGNIN);
        }
    }

    final protected function redirect(string $url, string $error = '', array $old = []): never
    {
        if ($error !== '') {
            $this->session->flashError($error);
        }

        if ($old !== []) {
            $this->session->flashOld($old);
        }

        $this->response->redirect($url);

        throw new \RuntimeException("Failed to redirect to {$url}");
    }

    final protected function redirectSelf(string $error = '', array $old = []): void
    {
        $this->redirect($this->request->path(), $error, $old);
    }

    final protected function ensureValidForm(Form $form, ?string $redirect = null): bool
    {
        $redirect ??= $this->request->path();

        $error = $this->checkCsrf($form->token()) ?? $form->validate();

        if ($error !== null) {
            $this->redirect($redirect, $error, $form->old());
            return false;
        }

        return true;
    }

    final protected function checkCsrf(string $token): ?string
    {
        return $this->csrf->verify($token) ? null : static::ERROR_CSRF;
    }

    final protected function render(string $view, array $data = [], bool $useLayout = true): void
    {
        extract($this->viewData($data), EXTR_SKIP);

        ob_start();
        require $this->viewFile($view);
        $content = (string) ob_get_clean();

        if (!$useLayout) {
            echo $content;
            return;
        }

        require $this->viewFile(static::LAYOUT);
    }

    final protected function userId(): ?int
    {
        $id = $this->session->userId();

        if ($id === null) {
            $this->redirect(Routes::USER_SIGNIN);
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
        $path = __DIR__ . '/../../Views/' . $view . '.php';

        if (!is_file($path)) {
            throw new \RuntimeException("View not found: {$view}");
        }

        return $path;
    }
}
