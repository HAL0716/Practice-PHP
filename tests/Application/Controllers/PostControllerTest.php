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
    public function testCreatePostSuccess(): void
    {
        $session = new FakeSession();
        $posts = new FakePostRepository();

        $session->set('user_id', 1);

        $posts->createResult = new Post(1, 1, 'hello', '2024-01-01 00:00:00', 'name');

        $controller = $this->createController(
            request: new FakeRequest(
                post: ['token' => 'token', 'comment' => 'hello'],
                method: 'POST',
                path: '/post/home'
            ),
            session: $session,
            posts: $posts
        );

        $response = $controller->home();

        $this->assertSame('/post/home', $response->getHeader('Location'));
        $this->assertSame(302, $response->getStatusCode());
    }

    public function testCreatePostFails(): void
    {
        $session = new FakeSession();
        $posts = new FakePostRepository();

        $session->set('user_id', 1);
        $posts->createResult = null;

        $controller = $this->createController(
            request: new FakeRequest(
                post: ['token' => 'token', 'comment' => 'hello'],
                method: 'POST',
                path: '/post/home'
            ),
            session: $session,
            posts: $posts
        );

        $response = $controller->home();

        $this->assertSame(PostController::ERROR_SYSTEM, $session->error());
        $this->assertSame('/post/home', $response->getHeader('Location'));
    }

    public function testDeletePostSuccess(): void
    {
        $session = new FakeSession();
        $posts = new FakePostRepository();

        $session->set('user_id', 1);
        $posts->deleteResult = true;

        $controller = $this->createController(
            request: new FakeRequest(
                post: ['token' => 'token', 'id' => '1'],
                method: 'POST',
                path: '/post/home'
            ),
            session: $session,
            posts: $posts
        );

        $response = $controller->delete();

        $this->assertSame('/post/home', $response->getHeader('Location'));
        $this->assertSame(302, $response->getStatusCode());
    }

    public function testDeletePostFails(): void
    {
        $session = new FakeSession();
        $posts = new FakePostRepository();

        $session->set('user_id', 1);
        $posts->deleteResult = false;

        $controller = $this->createController(
            request: new FakeRequest(
                post: ['token' => 'token', 'id' => '1'],
                method: 'POST',
                path: '/post/home'
            ),
            session: $session,
            posts: $posts
        );

        $response = $controller->delete();

        $this->assertSame(PostController::ERROR_SYSTEM, $session->error());
        $this->assertSame('/post/home', $response->getHeader('Location'));
    }

    public function testRequiresLogin(): void
    {
        $controller = $this->createController(
            request: new FakeRequest(method: 'GET')
        );

        $response = $controller->home();

        $this->assertSame('/user/signin', $response->getHeader('Location'));
    }

    public function testCsrfFails(): void
    {
        $session = new FakeSession();
        $session->set('user_id', 1);

        $controller = $this->createController(
            request: new FakeRequest(
                post: ['token' => 'invalid', 'comment' => 'hello'],
                method: 'POST',
                path: '/post/home'
            ),
            session: $session,
            csrf: new FakeCsrf('token')
        );

        $response = $controller->home();

        $this->assertSame(PostController::ERROR_CSRF, $session->error());
        $this->assertSame('/post/home', $response->getHeader('Location'));
    }

    private function createController(
        ?FakeRequest $request = null,
        ?FakeSession $session = null,
        ?FakeCsrf $csrf = null,
        ?FakePostRepository $posts = null
    ): PostController {
        return new PostController(
            $request ?? new FakeRequest(),
            $session ?? new FakeSession(),
            $csrf ?? new FakeCsrf(),
            $posts ?? new FakePostRepository()
        );
    }
}
