<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversNothing;
use App\Application\App;
use App\Application\Http\SessionInterface;
use App\Application\Security\CsrfInterface;
use App\Infrastructure\Database\DatabaseInterface;

#[CoversNothing]
final class AuthFlowTest extends TestCase
{
    public function testSignupRedirectsToHome(): void
    {
        $container = require __DIR__ . '/../../src/Bootstrap/dependencies.php';

        $db = $container->get(DatabaseInterface::class);
        $db->beginTransaction();

        $_POST = [
            'token' => $container->get(CsrfInterface::class)->token(),
            'name' => 'テストユーザー',
            'mail' => 'test@example.com',
            'pass' => 'pass1234',
            'pass_confirm' => 'pass1234',
        ];

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = '/user/signup';

        $app = $container->get(App::class);

        $response = $app->run();

        $this->assertSame('/post/home', $response->getHeader('Location'));

        $session = $container->get(SessionInterface::class);
        $this->assertTrue($session->isLoggedIn());

        $db->rollBack();
    }

}
