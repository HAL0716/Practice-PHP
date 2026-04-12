<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Application\App;
use App\Application\Http\ResponseInterface;
use App\Application\Http\SessionInterface;
use App\Application\Security\CsrfInterface;
use App\Bootstrap\Container;
use App\Domain\Post\PostRepositoryInterface;
use App\Domain\User\User;
use App\Domain\User\UserRepositoryInterface;
use App\Infrastructure\Database\DatabaseInterface;
use PHPUnit\Framework\TestCase;

abstract class IntegrationTestCase extends TestCase
{
    protected const DEFAULT_EMAIL = 'test@example.com';
    protected const DEFAULT_PASSWORD = 'password123';

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

    protected function getResponse(string $method, string $uri, array $post = [])
    {
        $_SERVER['REQUEST_METHOD'] = $method;
        $_SERVER['REQUEST_URI'] = $uri;
        $_POST = $post;

        return $this->app->run();
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

    final protected function createUser(): void
    {
        $this->users()->create('Test User', self::DEFAULT_EMAIL, self::DEFAULT_PASSWORD);
    }

    protected function login(User $user): void
    {
        $this->session()->login($user);
    }
}
