<?php

declare(strict_types=1);

namespace Tests\Controllers;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use App\Controllers\PostController;
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
        $request = new FakeRequest(
            post: ['token' => 'token', 'comment' => 'hello'],
            method: 'POST',
            path: '/post/home'
        );

        [$session, $response, $csrf, $posts] = $this->dependencies();

        $session->set('user_id', 1);

        $posts->createResult = new Post(1, 1, 'test', '2024-01-01 00:00:00', 'name');

        $controller = $this->newController($request, $session, $response, $csrf, $posts);

        $this->runWithRedirect(fn () => $controller->home());

        $this->assertSame('/post/home', $response->redirectTo);
    }

    public function testCreatePostFails(): void
    {
        $request = new FakeRequest(
            post: ['token' => 'token', 'comment' => 'hello'],
            method: 'POST'
        );

        [$session, $response, $csrf, $posts] = $this->dependencies();

        $session->set('user_id', 1);

        $posts->createResult = null;

        $controller = $this->newController($request, $session, $response, $csrf, $posts);

        $this->runWithRedirect(fn () => $controller->home());

        $this->assertSame(PostController::ERROR_SYSTEM, $session->error());
    }

    public function testDeletePostSuccess(): void
    {
        $request = new FakeRequest(
            post: ['token' => 'token', 'id' => '1'],
            method: 'POST'
        );

        [$session, $response, $csrf, $posts] = $this->dependencies();

        $session->set('user_id', 1);

        $controller = $this->newController($request, $session, $response, $csrf, $posts);

        $this->runWithRedirect(fn () => $controller->delete());

        $this->assertSame('/post/home', $response->redirectTo);
    }

    public function testDeletePostFails(): void
    {
        $request = new FakeRequest(
            post: ['token' => 'token', 'id' => '1'],
            method: 'POST'
        );

        [$session, $response, $csrf, $posts] = $this->dependencies();

        $session->set('user_id', 1);

        $posts->deleteResult = false;

        $controller = $this->newController($request, $session, $response, $csrf, $posts);

        $this->runWithRedirect(fn () => $controller->delete());

        $this->assertSame(PostController::ERROR_SYSTEM, $session->error());
    }

    private function runWithRedirect(callable $fn): void
    {
        try {
            $fn();
        } catch (RedirectException) {
        }
    }

    private function dependencies(): array
    {
        return [
            new FakeSession(),
            new FakeResponse(),
            new FakeCsrf(),
            new FakePostRepository(),
        ];
    }

    private function newController(
        FakeRequest $request,
        FakeSession $session,
        FakeResponse $response,
        FakeCsrf $csrf,
        FakePostRepository $posts
    ): PostController {
        return new PostController(
            $request,
            $session,
            $response,
            $csrf,
            $posts
        );
    }
}
