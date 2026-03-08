<?php

declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    protected const LAYOUT = 'layouts/default';

    private const ERROR_CSRF = '不正なリクエストです。再度お試しください';

    protected function render(
        string $view,
        array $data = [],
        bool $useLayout = true
    ): void {
        extract($data, EXTR_SKIP);

        ob_start();
        require $this->viewFile($view);
        $content = ob_get_clean();

        if (!$useLayout) {
            echo $content;
            return;
        }

        require $this->viewFile(static::LAYOUT);
    }

    protected function redirect(
        string $url,
        string $error = '',
        array $old = []
    ): void {
        if ($error !== '') {
            \App\Core\Session::flashError($error);
        }

        if ($old !== []) {
            \App\Core\Session::flashOld($old);
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
        $this->redirect(\App\Core\Request::path(), $error, $old);
    }

    protected function requireLogin(): void
    {
        if (!\App\Core\Session::isLoggedIn()) {
            $this->redirect(\App\Constants\Routes::SIGNIN);
        }
    }

    final protected function checkCsrf(string $token): ?string
    {
        return \App\Core\Csrf::verify($token) ? null : self::ERROR_CSRF;
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
