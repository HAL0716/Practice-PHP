<?php

declare(strict_types=1);

abstract class Controller
{
    protected const LAYOUT = 'layouts/default';

    protected function render(
        string $view,
        array $data = [],
        bool $useLayout = true
    ): void {
        // 変数展開（衝突防止）
        extract($data, EXTR_SKIP);

        ob_start();
        include $this->viewPath($view);;
        $content = ob_get_clean();

        if ($useLayout) {
            include $this->viewPath(static::LAYOUT);
            return;
        }

        echo $content;
    }

    private function viewPath(string $name): string
    {
        return __DIR__ . '/../views/' . $name . '.php';
        if (!file_exists($path)) {
            throw new RuntimeException("View not found: {$name}");
        }
    }

    protected function redirect(string $url): void
    {
        if (!headers_sent()) {
            header('Location: ' . $url);
        }
        exit;
    }

    protected function redirectSelf(): void
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $this->redirect($uri);
    }

    protected function isLoggedIn(): bool
    {
        return Session::has('user_id');
    }
}
