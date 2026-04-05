<?php

declare(strict_types=1);

namespace Tests\Infrastructure\Http;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use App\Infrastructure\Http\Request;

#[CoversClass(Request::class)]
final class RequestTest extends TestCase
{
    protected function setUp(): void
    {
        $_GET = [];
        $_POST = [];
        $_SERVER = [];
    }

    public function testPostReturnsValue(): void
    {
        $_POST['name'] = 'name';

        $request = new Request();

        $this->assertSame('name', $request->post('name'));
    }

    public function testPostReturnsDefault(): void
    {
        $request = new Request();

        $this->assertSame('default', $request->post('name', 'default'));
    }

    public function testPostReturnsDefaultWhenArray(): void
    {
        $_POST['name'] = ['array'];

        $request = new Request();

        $this->assertSame('default', $request->post('name', 'default'));
    }

    public function testQueryReturnsValue(): void
    {
        $_GET['q'] = 'search';

        $request = new Request();

        $this->assertSame('search', $request->query('q'));
    }

    public function testQueryArrayReturnsArray(): void
    {
        $_GET['ids'] = [1, 2];

        $request = new Request();

        $this->assertSame([1, 2], $request->queryArray('ids'));
    }

    public function testQueryArrayReturnsDefaultWhenNotArray(): void
    {
        $_GET['ids'] = 'not-array';

        $request = new Request();

        $this->assertSame([], $request->queryArray('ids'));
    }

    public function testIsGet(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $request = new Request();

        $this->assertTrue($request->isGet());
        $this->assertFalse($request->isPost());
    }

    public function testIsPost(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $request = new Request();

        $this->assertTrue($request->isPost());
        $this->assertFalse($request->isGet());
    }

    public function testPathReturnsPath(): void
    {
        $_SERVER['REQUEST_URI'] = '/posts?id=1';

        $request = new Request();

        $this->assertSame('/posts', $request->path());
    }

    public function testPathReturnsSlashWhenMissing(): void
    {
        $request = new Request();

        $this->assertSame('/', $request->path());
    }
}
