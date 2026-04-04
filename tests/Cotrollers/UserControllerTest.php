<?php

declare(strict_types=1);

namespace Tests\Controllers;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use App\Controllers\UserController;
use App\Domain\User\User;
use Tests\Fake\Http\FakeRequest;
use Tests\Fake\Http\FakeSession;
use Tests\Fake\Http\FakeResponse;
use Tests\Fake\Http\RedirectException;
use Tests\Fake\Security\FakeCsrf;
use Tests\Fake\Security\FakeLoginThrottle;
use Tests\Fake\Domain\FakeUserRepository;

#[CoversClass(UserController::class)]
final class UserControllerTest extends TestCase
{
    public function testSignupSuccess(): void
    {
        $request = new FakeRequest(post: $this->validSignupData(), method: 'POST');

        [$session, $response, $csrf, $users, $throttle] = $this->dependencies();

        $users->createResult = new User(1, 'name', 'test@example.com', 'hash');

        $controller = $this->newController($request, $session, $response, $csrf, $users, $throttle);

        $this->runWithRedirect(fn () => $controller->signup());

        $this->assertTrue($session->isLoggedIn());
        $this->assertSame('/post/home', $response->redirectTo);
    }

    public function testSignupFailsWhenEmailExists(): void
    {
        $request = new FakeRequest(post: $this->validSignupData(), method: 'POST');

        [$session, $response, $csrf, $users, $throttle] = $this->dependencies();

        $users->findByEmailResult = new User(1, 'name', 'test@example.com', 'hash');

        $controller = $this->newController($request, $session, $response, $csrf, $users, $throttle);

        $this->runWithRedirect(fn () => $controller->signup());

        $this->assertSame(UserController::ERROR_EXISTS, $session->error());
    }

    public function testSigninSuccess(): void
    {
        $request = new FakeRequest(post: $this->validSigninData(), method: 'POST');

        [$session, $response, $csrf, $users, $throttle] = $this->dependencies();

        $user = new User(1, 'name', 'test@example.com', password_hash('pass1234', PASSWORD_DEFAULT));
        $users->findByEmailResult = $user;

        $controller = $this->newController($request, $session, $response, $csrf, $users, $throttle);

        $this->runWithRedirect(fn () => $controller->signin());

        $this->assertTrue($session->isLoggedIn());
        $this->assertTrue($throttle->cleared);
        $this->assertSame('/post/home', $response->redirectTo);
    }

    public function testSigninFails(): void
    {
        $request = new FakeRequest(
            post: ['token' => 'token', 'mail' => 'test@example.com', 'pass' => 'wrong'],
            method: 'POST'
        );

        [$session, $response, $csrf, $users, $throttle] = $this->dependencies();

        $controller = $this->newController($request, $session, $response, $csrf, $users, $throttle);

        $this->runWithRedirect(fn () => $controller->signin());

        $this->assertSame(UserController::ERROR_LOGIN, $session->error());
    }

    public function testSigninLocked(): void
    {
        $request = new FakeRequest(
            post: ['token' => 'token', 'mail' => 'test@example.com', 'pass' => 'wrong'],
            method: 'POST'
        );

        [$session, $response, $csrf, $users, $throttle] = $this->dependencies();

        $throttle->hitResult = 'locked';

        $controller = $this->newController($request, $session, $response, $csrf, $users, $throttle);

        $this->runWithRedirect(fn () => $controller->signin());

        $this->assertSame('locked', $session->error());
    }

    public function testSignout(): void
    {
        $request = new FakeRequest(method: 'GET');

        [$session, $response, $csrf, $users, $throttle] = $this->dependencies();

        $session->set('user_id', 1);

        $controller = $this->newController($request, $session, $response, $csrf, $users, $throttle);

        $this->runWithRedirect(fn () => $controller->signout());

        $this->assertFalse($session->isLoggedIn());
        $this->assertSame('/user/signin', $response->redirectTo);
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
            new FakeUserRepository(),
            new FakeLoginThrottle(),
        ];
    }

    private function newController(
        FakeRequest $request,
        FakeSession $session,
        FakeResponse $response,
        FakeCsrf $csrf,
        FakeUserRepository $users,
        FakeLoginThrottle $throttle
    ): UserController {
        return new UserController($request, $session, $response, $csrf, $users, $throttle);
    }

    private function validSignupData(): array
    {
        return [
            'token' => 'token',
            'name' => 'name',
            'mail' => 'test@example.com',
            'pass' => 'pass1234',
            'pass_confirm' => 'pass1234',
        ];
    }

    private function validSigninData(): array
    {
        return [
            'token' => 'token',
            'mail' => 'test@example.com',
            'pass' => 'pass1234',
        ];
    }
}
