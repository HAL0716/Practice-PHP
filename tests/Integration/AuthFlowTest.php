<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversNothing;
use App\Application\App;
use App\Application\Http\SessionInterface;
use App\Application\Security\CsrfInterface;
use App\Domain\User\UserRepositoryInterface;
use App\Infrastructure\Database\DatabaseInterface;

#[CoversNothing]
final class AuthFlowTest extends TestCase
{
    private $container;
    private $db;

    protected function setUp(): void
    {
        $this->container = require __DIR__ . '/../../src/Bootstrap/dependencies.php';

        $this->db = $this->container->get(DatabaseInterface::class);
        $this->db->beginTransaction();
    }

    protected function tearDown(): void
    {
        $this->db->rollBack();
    }

    public function testSignupRedirectsToHome(): void
    {
        $_POST = [
            'token' => $this->container->get(CsrfInterface::class)->token(),
            'name' => 'テストユーザー',
            'mail' => 'test@example.com',
            'pass' => 'pass1234',
            'pass_confirm' => 'pass1234',
        ];

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = '/user/signup';

        $app = $this->container->get(App::class);

        $response = $app->run();

        $this->assertSame('/post/home', $response->getHeader('Location'));

        $session = $this->container->get(SessionInterface::class);
        $this->assertTrue($session->isLoggedIn());
    }

    public function testSigninRedirectsToHome(): void
    {
        $user = $this->container->get(UserRepositoryInterface::class);
        $user->create('テストユーザー', 'test@example.com', 'pass1234');

        $_POST = [
            'token' => $this->container->get(CsrfInterface::class)->token(),
            'mail' => 'test@example.com',
            'pass' => 'pass1234',
        ];

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = '/user/signin';

        $app = $this->container->get(App::class);

        $response = $app->run();

        $this->assertSame('/post/home', $response->getHeader('Location'));

        $session = $this->container->get(SessionInterface::class);
        $this->assertTrue($session->isLoggedIn());
    }
}
