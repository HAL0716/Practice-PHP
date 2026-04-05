<?php

declare(strict_types=1);

namespace Tests\Fake\Infrastructure\Http;

use App\Application\Http\ResponseInterface;

final class RedirectException extends \RuntimeException
{
}

final class FakeResponse implements ResponseInterface
{
    public ?string $redirectTo;

    public function __construct()
    {
        $this->redirectTo = null;
    }

    public function redirect(string $url): void
    {
        $this->redirectTo = $url;
        throw new RedirectException("Redirected to {$url}");
    }
}
