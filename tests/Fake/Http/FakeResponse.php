<?php

declare(strict_types=1);

namespace Tests\Fake\Http;

use App\Contracts\Http\ResponseInterface;

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
    }
}
