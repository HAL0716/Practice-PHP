<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Application\App;
use App\Application\Http\SessionInterface;
use App\Application\Security\CsrfInterface;
use App\Bootstrap\Container;
use App\Infrastructure\Database\DatabaseInterface;
use PHPUnit\Framework\TestCase;

abstract class IntegrationTestCase extends TestCase
{
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

    protected function csrfToken(): string
    {
        return $this->container->get(CsrfInterface::class)->token();
    }

    protected function session(): SessionInterface
    {
        return $this->container->get(SessionInterface::class);
    }
}
