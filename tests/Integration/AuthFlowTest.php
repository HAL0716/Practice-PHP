<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversNothing;
use App\Application\App;
use App\Application\Http\ResponseInterface;
use App\Application\Http\SessionInterface;
use App\Application\Security\CsrfInterface;
use App\Domain\User\UserRepositoryInterface;
use App\Infrastructure\Database\DatabaseInterface;

#[CoversNothing]
final class AuthFlowTest extends TestCase
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

    public function testSignup(): void
    {
        $response = $this->getResponse('POST', '/user/signup', [
            'token' => $this->csrf(),
            'name' => 'テストユーザー',
            'mail' => 'test@example.com',
            'pass' => 'pass1234',
            'pass_confirm' => 'pass1234',
        ]);

        $this->assertTrue($this->session()->isLoggedIn());
        $this->assertSame('/post/home', $response->getHeader('Location'));
    }

    public function testSignin(): void
    {
        $this->users()->create('テストユーザー', 'test@example.com', 'pass1234');

        $response = $this->getResponse('POST', '/user/signin', [
            'token' => $this->csrf(),
            'mail' => 'test@example.com',
            'pass' => 'pass1234',
        ]);

        $this->assertTrue($this->session()->isLoggedIn());
        $this->assertSame('/post/home', $response->getHeader('Location'));
    }

    public function testSignout(): void
    {
        $this->users()->create('テストユーザー', 'test@example.com', 'pass1234');

        $this->session()->login($this->users()->findByEmail('test@example.com'));

        $response = $this->getResponse('GET', '/user/signout');

        $this->assertFalse($this->session()->isLoggedIn());
        $this->assertSame('/user/signin', $response->getHeader('Location'));
    }

    public function testDelete(): void
    {
        $this->users()->create('テストユーザー', 'test@example.com', 'pass1234');

        $this->session()->login($this->users()->findByEmail('test@example.com'));

        $response = $this->getResponse('POST', '/user/delete', [
            'token' => $this->csrf(),
            'pass_current' => 'pass1234',
        ]);

        $this->assertFalse($this->session()->isLoggedIn());
        $this->assertSame('/user/signin', $response->getHeader('Location'));
    }

    private function getResponse(string $method, string $uri, array $post = []): ResponseInterface
    {
        $_SERVER['REQUEST_METHOD'] = $method;
        $_SERVER['REQUEST_URI'] = $uri;

        $_POST = $post;

        return $this->app->run();
    }

    private function csrf(): string
    {
        return $this->container->get(CsrfInterface::class)->token();
    }

    private function session(): SessionInterface
    {
        return $this->container->get(SessionInterface::class);
    }

    private function users(): UserRepositoryInterface
    {
        return $this->container->get(UserRepositoryInterface::class);
    }
}
