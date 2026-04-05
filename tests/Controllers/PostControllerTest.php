<?php

declare(strict_types=1);

namespace Tests\Controllers;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use App\Application\Controllers\PostController;
use App\Domain\Post\Post;
use Tests\Fake\Http\FakeRequest;
use Tests\Fake\Http\FakeSession;
use Tests\Fake\Http\FakeResponse;
use Tests\Fake\Http\RedirectException;
use Tests\Fake\Security\FakeCsrf;
use Tests\Fake\Domain\FakePostRepository;

#[CoversClass(PostController::class)]
final class PostControllerTest extends TestCase
{
    public function testCreatePostSuccess(): void
    {
        $session = new FakeSession();
        $response = new FakeResponse();
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
            response: $response,
            posts: $posts
        );

        $this->expectException(RedirectException::class);

        try {
            $controller->home();
        } finally {
            $this->assertSame('/post/home', $response->redirectTo);
        }
    }

    public function testCreatePostFails(): void
    {
        $session = new FakeSession();
        $response = new FakeResponse();
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
            response: $response,
            posts: $posts
        );

        $this->expectException(RedirectException::class);

        try {
            $controller->home();
        } finally {
            $this->assertSame(PostController::ERROR_SYSTEM, $session->error());
        }
    }

    public function testDeletePostSuccess(): void
    {
        $session = new FakeSession();
        $response = new FakeResponse();
        $posts = new FakePostRepository();

        $session->set('user_id', 1);

        $controller = $this->createController(
            request: new FakeRequest(
                post: ['token' => 'token', 'id' => '1'],
                method: 'POST',
                path: '/post/home'
            ),
            session: $session,
            response: $response,
            posts: $posts
        );

        $this->expectException(RedirectException::class);

        try {
            $controller->delete();
        } finally {
            $this->assertSame('/post/home', $response->redirectTo);
        }
    }

    public function testDeletePostFails(): void
    {
        $session = new FakeSession();
        $response = new FakeResponse();
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
            response: $response,
            posts: $posts
        );

        $this->expectException(RedirectException::class);

        try {
            $controller->delete();
        } finally {
            $this->assertSame(PostController::ERROR_SYSTEM, $session->error());
        }
    }

    public function testRequiresLogin(): void
    {
        $response = new FakeResponse();

        $controller = $this->createController(
            request: new FakeRequest(method: 'GET'),
            response: $response
        );

        $this->expectException(RedirectException::class);

        try {
            $controller->home();
        } finally {
            $this->assertSame('/user/signin', $response->redirectTo);
        }
    }

    public function testCsrfFails(): void
    {
        $session = new FakeSession();
        $response = new FakeResponse();

        $session->set('user_id', 1);

        $controller = $this->createController(
            request: new FakeRequest(
                post: ['token' => 'invalid', 'comment' => 'hello'],
                method: 'POST'
            ),
            session: $session,
            response: $response,
            csrf: new FakeCsrf('token')
        );

        $this->expectException(RedirectException::class);

        try {
            $controller->home();
        } finally {
            $this->assertSame(PostController::ERROR_CSRF, $session->error());
        }
    }

    private function createController(
        ?FakeRequest $request = null,
        ?FakeSession $session = null,
        ?FakeResponse $response = null,
        ?FakeCsrf $csrf = null,
        ?FakePostRepository $posts = null
    ): PostController {
        return new PostController(
            $request ?? new FakeRequest(),
            $session ?? new FakeSession(),
            $response ?? new FakeResponse(),
            $csrf ?? new FakeCsrf(),
            $posts ?? new FakePostRepository()
        );
    }
}
