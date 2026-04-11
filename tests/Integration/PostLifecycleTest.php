<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversNothing;
use App\Application\App;
use App\Application\Http\ResponseInterface;
use App\Application\Http\SessionInterface;
use App\Application\Security\CsrfInterface;
use App\Domain\User\UserRepositoryInterface;
use App\Domain\Post\PostRepositoryInterface;
use App\Infrastructure\Database\DatabaseInterface;

#[CoversNothing]
final class PostLifecycleTest extends TestCase
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

        $users = $this->container->get(UserRepositoryInterface::class);
        $users->create('テストユーザー', 'test@example.com', 'pass1234');
        $this->session()->login($users->findByEmail('test@example.com'));
    }

    protected function tearDown(): void
    {
        $_SERVER = [];
        $_POST = [];

        $this->db->rollBack();
    }

    public function testCreatePost(): void
    {
        $response = $this->getResponse('POST', '/post/home', [
            'token' => $this->csrf(),
            'comment' => 'テスト投稿'
        ]);

        $this->assertSame('/post/home', $response->getHeader('Location'));

        $posts = $this->posts()->findAll();
        $this->assertNotEmpty($posts);
    }

    public function testHomePage(): void
    {
        $response = $this->getResponse('GET', '/post/home');

        $this->assertSame(200, $response->getStatusCode());
    }

    public function testDeletePost(): void
    {
        $userId = $this->session()->userId();
        $postId = $this->posts()->create($userId, '削除対象')->id();

        $response = $this->getResponse('POST', '/post/delete', [
            'token' => $this->csrf(),
            'id' => $postId
        ]);

        $this->assertSame('/post/home', $response->getHeader('Location'));

        $this->assertNull($this->posts()->findById($postId));
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

    private function posts(): PostRepositoryInterface
    {
        return $this->container->get(PostRepositoryInterface::class);
    }
}
