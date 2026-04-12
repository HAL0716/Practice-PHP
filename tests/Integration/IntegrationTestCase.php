<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Application\App;
use App\Application\Http\ResponseInterface;
use App\Application\Http\SessionInterface;
use App\Application\Security\CsrfInterface;
use App\Bootstrap\Container;
use App\Domain\Post\PostRepositoryInterface;
use App\Domain\User\UserRepositoryInterface;
use App\Infrastructure\Database\DatabaseInterface;
use PHPUnit\Framework\TestCase;

abstract class IntegrationTestCase extends TestCase
{
    protected const SIGNIN_URL = '/user/signin';
    protected const HOME_URL = '/post/home';

    protected App $app;
    protected Container $container;
    protected DatabaseInterface $db;

    protected function setUp(): void
    {
        $this->container = require __DIR__ . '/../../src/Bootstrap/dependencies.php';

        $this->db = $this->container->get(DatabaseInterface::class);
        $this->db->beginTransaction();

        $this->app = $this->container->get(App::class);

        $this->session()->clear();
    }

    protected function tearDown(): void
    {
        $_SERVER = [];
        $_POST = [];
        $_SESSION = [];

        $this->session()->clear();

        $this->db->rollBack();
    }

    private function getResponse(string $method, string $uri, array $post = [])
    {
        $_SERVER['REQUEST_METHOD'] = $method;
        $_SERVER['REQUEST_URI'] = $uri;
        $_POST = $post;

        return $this->app->run();
    }

    protected function get(string $uri): ResponseInterface
    {
        return $this->getResponse('GET', $uri);
    }

    protected function post(string $uri, array $data = []): ResponseInterface
    {
        return $this->getResponse('POST', $uri, $data);
    }

    final protected function assertError(string $expectedError): void
    {
        $this->assertSame($expectedError, $this->session()->error());
    }

    final protected function assertRedirect(ResponseInterface $response, string $location): void
    {
        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame($location, $response->getHeader('Location'));
    }

    protected function csrfToken(): string
    {
        return $this->container->get(CsrfInterface::class)->token();
    }

    protected function session(): SessionInterface
    {
        return $this->container->get(SessionInterface::class);
    }

    protected function users(): UserRepositoryInterface
    {
        return $this->container->get(UserRepositoryInterface::class);
    }

    protected function posts(): PostRepositoryInterface
    {
        return $this->container->get(PostRepositoryInterface::class);
    }
}
