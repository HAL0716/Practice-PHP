<?php

declare(strict_types=1);

namespace Tests\Core\Http;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use App\Core\Http\Response;

#[CoversClass(Response::class)]
final class ResponseTest extends TestCase
{
    private Response $response;

    protected function setUp(): void
    {
        $this->response = new Response();
    }

    public function testRedirectDoesNotThrow(): void
    {
        $this->response->redirect('/test');

        $this->assertTrue(true);
    }

    public function testRedirectCanBeCalledMultipleTimes(): void
    {
        $this->response->redirect('/a');
        $this->response->redirect('/b');

        $this->assertTrue(true);
    }
}
