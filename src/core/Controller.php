<?php

declare(strict_types=1);

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
        include $this->viewPath($view);
        $content = ob_get_clean();

        if ($useLayout) {
            include $this->viewPath(static::LAYOUT);
            return;
        }

        echo $content;
    }

    private function viewPath(string $name): string
    {
        $path = __DIR__ . '/../views/' . $name . '.php';
        if (!file_exists($path)) {
            throw new RuntimeException("View not found: {$name}");
        }
        return $path;
    }

    protected function redirect(string $url, string $error = '', array $old = []): void
    {
        if ($error) {
            Session::flash(SessionKeys::ERRORS, $error);
        }
        if ($old) {
            Session::flash(SessionKeys::OLD, $old);
        }
        if (!headers_sent()) {
            header('Location: ' . $url);
        }
        exit;
    }

    protected function redirectSelf(string $error = '', array $old = []): void
    {
        $this->redirect(Request::path(), $error, $old);
    }

    protected function requireLogin(): void
    {
        if (!Session::isLoggedIn()) {
            $this->redirect(Routes::SIGNIN);
        }
    }

    protected function flashOld(array $data): void
    {
        Session::flash(SessionKeys::OLD, $data);
    }

    final protected function checkCsrf(string $token): void
    {
        if (!Csrf::verify($token)) {
            Session::flash(SessionKeys::ERRORS, self::ERROR_CSRF);
            $this->redirectSelf();
        }
    }
}
