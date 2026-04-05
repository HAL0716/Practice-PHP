<?php

declare(strict_types=1);

namespace Tests\Core\Http;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use App\Infrastructure\Http\Response;

#[CoversClass(Response::class)]
final class ResponseTest extends TestCase
{
    public function testRedirectSendsHeader(): void
    {
        $captured = null;

        $response = new Response(
            headerSender: function (string $header) use (&$captured) {
                $captured = $header;
            }
        );

        $response->redirect('/home');

        $this->assertSame('Location: /home', $captured);
    }
}
