<?php

declare(strict_types=1);

namespace Tests\Application\Controllers;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use App\Application\Controllers\PostController;
use App\Domain\Post\Post;
use Tests\Fake\Domain\FakePostRepository;
use Tests\Fake\Infrastructure\Http\FakeRequest;
use Tests\Fake\Infrastructure\Http\FakeSession;
use Tests\Fake\Infrastructure\Security\FakeCsrf;

#[CoversClass(PostController::class)]
final class PostControllerTest extends TestCase
{
    private FakeSession $session;
    private FakePostRepository $posts;
    private FakeCsrf $csrf;

    protected function setUp(): void
    {
        $this->session = new FakeSession();
        $this->posts = new FakePostRepository();
        $this->csrf = new FakeCsrf();
    }

    public function testCreatePostSuccess(): void
    {
        $this->session->set('user_id', 1);

        $this->posts->createResult = $this->createPost();

        $controller = $this->createController([
            'request' => $this->createRequest()
        ]);

        $response = $controller->home();

        $this->assertSame('/post/home', $response->getHeader('Location'));
    }

    public function testCreatePostFails(): void
    {
        $this->session->set('user_id', 1);
        $this->posts->createResult = null;

        $controller = $this->createController([
            'request' => $this->createRequest()
        ]);

        $controller->home();

        $this->assertSame(PostController::ERROR_SYSTEM, $this->session->error());
    }

    public function testDeletePostSuccess(): void
    {
        $this->session->set('user_id', 1);
        $this->posts->deleteResult = true;

        $controller = $this->createController([
            'request' => $this->deleteRequest()
        ]);

        $response = $controller->delete();

        $this->assertSame('/post/home', $response->getHeader('Location'));
    }

    public function testDeletePostFails(): void
    {
        $this->session->set('user_id', 1);
        $this->posts->deleteResult = false;

        $controller = $this->createController([
            'request' => $this->deleteRequest()
        ]);

        $controller->delete();

        $this->assertSame(PostController::ERROR_SYSTEM, $this->session->error());
    }

    private function createPost(): Post
    {
        return new Post(1, 1, 'hello', '2024-01-01 00:00:00', 'name');
    }

    private function createController(array $overrides): PostController
    {
        return new PostController(
            $overrides['request'] ?? new FakeRequest(),
            $overrides['session'] ?? $this->session,
            $overrides['csrf'] ?? $this->csrf,
            $overrides['posts'] ?? $this->posts
        );
    }

    private function createRequest(): FakeRequest
    {
        return new FakeRequest(
            post: ['token' => 'token', 'comment' => 'テスト投稿'],
            method: 'POST',
            path: '/post/home'
        );
    }

    private function deleteRequest(): FakeRequest
    {
        return new FakeRequest(
            post: ['token' => 'token', 'id' => '1'],
            method: 'POST',
            path: '/post/delete'
        );
    }
}
