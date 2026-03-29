<?php

declare(strict_types=1);

namespace Tests\Core\Http;

use App\Contracts\Http\RequestInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use App\Core\Http\Request;

#[CoversClass(Request::class)]
final class RequestTest extends TestCase
{
    private Request $request;

    protected function setUp(): void
    {
        $_POST = [];
        $_GET = [];
        $_SERVER = [];

        $this->request = new Request();
    }

    public function testImplementsInterface(): void
    {
        $this->assertInstanceOf(RequestInterface::class, $this->request);
    }

    public function testPost(): void
    {
        $_POST['key'] = 'value';

        $this->assertSame('value', $this->request->post('key'));
    }

    public function testPostDefault(): void
    {
        $this->assertSame('default', $this->request->post('none', 'default'));
    }

    public function testPostArray(): void
    {
        $_POST['items'] = ['a', 'b'];

        $this->assertSame(['a', 'b'], $this->request->postArray('items'));
    }

    public function testPostArrayDefault(): void
    {
        $this->assertSame([], $this->request->postArray('none'));
    }

    public function testQuery(): void
    {
        $_GET['key'] = 'value';

        $this->assertSame('value', $this->request->query('key'));
    }

    public function testQueryDefault(): void
    {
        $this->assertSame('default', $this->request->query('none', 'default'));
    }

    public function testQueryArray(): void
    {
        $_GET['items'] = ['a', 'b'];

        $this->assertSame(['a', 'b'], $this->request->queryArray('items'));
    }

    public function testQueryArrayDefault(): void
    {
        $this->assertSame([], $this->request->queryArray('none'));
    }

    public function testMethod(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $this->assertTrue($this->request->isPost());
        $this->assertFalse($this->request->isGet());
    }

    public function testPath(): void
    {
        $_SERVER['REQUEST_URI'] = '/test/path?x=1';

        $this->assertSame('/test/path', $this->request->path());
    }
}
