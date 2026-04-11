<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversNothing;
use App\Application\App;
use App\Application\Http\ResponseInterface;
use App\Infrastructure\Database\DatabaseInterface;

#[CoversNothing]
final class AuthGuardTest extends TestCase
{
    private $container;
    private $db;
    private App $app;

    protected function setUp(): void
    {
        $this->container = require __DIR__ . '/../../src/Bootstrap/dependencies.php';

        $this->db = $this->container->get(DatabaseInterface::class);
        $this->db->beginTransaction();

        $this->app = $this->container->get(App::class);
    }

    protected function tearDown(): void
    {
        $_SERVER = [];
        $_POST = [];

        $this->db->rollBack();
    }

    public function testUserMypageRequiresLogin(): void
    {
        $response = $this->getResponse('GET', '/user/mypage');

        $this->assertSame('/user/signin', $response->getHeader('Location'));
    }

    public function testPostHomeRequiresLogin(): void
    {
        $response = $this->getResponse('GET', '/post/home');

        $this->assertSame('/user/signin', $response->getHeader('Location'));
    }

    public function testPostCreateRequiresLogin(): void
    {
        $response = $this->getResponse('POST', '/post/home', [
            'token' => 'dummy',
            'comment' => 'hello'
        ]);

        $this->assertSame('/user/signin', $response->getHeader('Location'));
    }

    public function testPostDeleteRequiresLogin(): void
    {
        $response = $this->getResponse('POST', '/post/delete', [
            'token' => 'dummy',
            'id' => 1
        ]);

        $this->assertSame('/user/signin', $response->getHeader('Location'));
    }

    private function getResponse(string $method, string $uri, array $post = []): ResponseInterface
    {
        $_SERVER['REQUEST_METHOD'] = $method;
        $_SERVER['REQUEST_URI'] = $uri;

        $_POST = $post;

        return $this->app->run();
    }
}
