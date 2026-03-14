<?php

declare(strict_types=1);

namespace App\Core\Http;

use App\Constants\Routes;
use App\Core\Security\Csrf;
use App\Core\Form;

abstract class Controller
{
    protected const LAYOUT = 'layouts/default';

    protected const ERROR_CSRF   = '不正なリクエストです。再度お試しください。';
    protected const ERROR_SYSTEM = 'システムエラーが発生しました。時間をおいて再度お試しください。';

    final protected function dispatch(?callable $post = null, ?callable $get = null): void
    {
        if (Request::isPost() && $post) {
            $post();
            return;
        }

        if (Request::isGet() && $get) {
            $get();
            return;
        }

        $this->redirectSelf(self::ERROR_SYSTEM);
    }

    protected function requireLogin(): void
    {
        if (!Session::isLoggedIn()) {
            $this->redirect(Routes::SIGNIN);
        }
    }

    protected function redirect(
        string $url,
        string $error = '',
        array $old = []
    ): void {
        if ($error !== '') {
            Session::flashError($error);
        }

        if ($old !== []) {
            Session::flashOld($old);
        }

        if (!headers_sent()) {
            header("Location: {$url}");
        }

        exit;
    }

    protected function redirectSelf(
        string $error = '',
        array $old = []
    ): void {
        $this->redirect(Request::path(), $error, $old);
    }

    protected function ensureValidForm(Form $form, ?string $redirect = null): bool
    {
        $redirect ??= Request::path();

        $error = $this->checkCsrf($form->token()) ?? $form->validate();

        if ($error !== null) {
            $this->redirect($redirect, $error, $form->old());
            return false;
        }

        return true;
    }

    final protected function checkCsrf(string $token): ?string
    {
        return Csrf::verify($token) ? null : static::ERROR_CSRF;
    }

    protected function render(
        string $view,
        array $data = [],
        bool $useLayout = true
    ): void {
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

    private function viewData(array $data): array
    {
        return $data + [
            'token' => Csrf::token(),
            'error' => Session::error(),
            'old'   => Session::old(),
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
