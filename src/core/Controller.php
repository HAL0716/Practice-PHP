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

    protected function redirect(string $url): void
    {
        if (!headers_sent()) {
            header('Location: ' . $url);
        }
        exit;
    }

    protected function redirectSelf(): void
    {
        $this->redirect(Request::path());
    }

    protected function requireLogin(): void
    {
        if (!Session::has(SessionKeys::USER_ID)) {
            $this->redirect(Routes::SIGNIN);
        }
    }

    protected function flashOld(array $data): void
    {
        Session::flash(SessionKeys::OLD, $data);
    }
}
