<?php

declare(strict_types=1);

namespace Tests\Infrastructure\Http;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use App\Infrastructure\Http\Response;

#[CoversClass(Response::class)]
final class ResponseTest extends TestCase
{
    public function testRedirectCreatesLocationHeader(): void
    {
        $response = Response::redirect('/home');

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('/home', $response->getHeader('Location'));
    }
}
