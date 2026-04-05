<?php

declare(strict_types=1);

namespace Tests\Controllers;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use App\Application\Controllers\UserController;
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
        $session = new FakeSession();
        $response = new FakeResponse();
        $users = new FakeUserRepository();

        $users->createResult = new User(1, 'name', 'test@example.com', 'hash');

        $controller = $this->createController(
            request: new FakeRequest(post: $this->validSignupData(), method: 'POST'),
            session: $session,
            response: $response,
            users: $users
        );

        $this->expectException(RedirectException::class);

        try {
            $controller->signup();
        } finally {
            $this->assertTrue($session->isLoggedIn());
            $this->assertSame('/post/home', $response->redirectTo);
        }
    }

    public function testSignupFailsWhenEmailExists(): void
    {
        $session = new FakeSession();
        $users = new FakeUserRepository();

        $users->findByEmailResult = new User(1, 'name', 'test@example.com', 'hash');

        $controller = $this->createController(
            request: new FakeRequest(post: $this->validSignupData(), method: 'POST'),
            session: $session,
            users: $users
        );

        $this->expectException(RedirectException::class);

        try {
            $controller->signup();
        } finally {
            $this->assertSame(UserController::ERROR_EXISTS, $session->error());
        }
    }

    public function testSignupFailsWhenCreateFails(): void
    {
        $session = new FakeSession();
        $users = new FakeUserRepository();

        $users->createResult = null;

        $controller = $this->createController(
            request: new FakeRequest(post: $this->validSignupData(), method: 'POST'),
            session: $session,
            users: $users
        );

        $this->expectException(RedirectException::class);

        try {
            $controller->signup();
        } finally {
            $this->assertSame(UserController::ERROR_SYSTEM, $session->error());
        }
    }

    public function testSigninSuccess(): void
    {
        $session = new FakeSession();
        $response = new FakeResponse();
        $users = new FakeUserRepository();
        $throttle = new FakeLoginThrottle();

        $users->findByEmailResult = new User(
            1,
            'name',
            'test@example.com',
            password_hash('pass1234', PASSWORD_DEFAULT)
        );

        $controller = $this->createController(
            request: new FakeRequest(post: $this->validSigninData(), method: 'POST'),
            session: $session,
            response: $response,
            users: $users,
            throttle: $throttle
        );

        $this->expectException(RedirectException::class);

        try {
            $controller->signin();
        } finally {
            $this->assertTrue($session->isLoggedIn());
            $this->assertTrue($throttle->cleared);
            $this->assertSame('/post/home', $response->redirectTo);
        }
    }

    public function testSigninFails(): void
    {
        $session = new FakeSession();
        $users = new FakeUserRepository();
        $throttle = new FakeLoginThrottle();

        $controller = $this->createController(
            request: new FakeRequest(post: $this->invalidSigninData(), method: 'POST'),
            session: $session,
            users: $users,
            throttle: $throttle
        );

        $this->expectException(RedirectException::class);

        try {
            $controller->signin();
        } finally {
            $this->assertSame(UserController::ERROR_LOGIN, $session->error());
        }
    }

    public function testSigninLocked(): void
    {
        $session = new FakeSession();
        $throttle = new FakeLoginThrottle();
        $throttle->hitResult = 'locked';

        $controller = $this->createController(
            request: new FakeRequest(post: $this->invalidSigninData(), method: 'POST'),
            session: $session,
            throttle: $throttle
        );

        $this->expectException(RedirectException::class);

        try {
            $controller->signin();
        } finally {
            $this->assertSame('locked', $session->error());
        }
    }

    public function testSignout(): void
    {
        $session = new FakeSession();
        $response = new FakeResponse();

        $session->set('user_id', 1);

        $controller = $this->createController(
            request: new FakeRequest(method: 'GET'),
            session: $session,
            response: $response
        );

        $this->expectException(RedirectException::class);

        try {
            $controller->signout();
        } finally {
            $this->assertFalse($session->isLoggedIn());
            $this->assertSame('/user/signin', $response->redirectTo);
        }
    }

    public function testDeleteSuccess(): void
    {
        $session = new FakeSession();
        $session->set('user_id', 1);

        $users = new FakeUserRepository();
        $users->deleteResult = true;
        $users->findByIdResult = new User(1, 'name', 'test@example.com', password_hash('pass1234', PASSWORD_DEFAULT));

        $controller = $this->createController(
            request: new FakeRequest(post: [
                'token' => 'token',
                'pass_current' => 'pass1234'
            ], method: 'POST'),
            session: $session,
            users: $users
        );

        $this->expectException(RedirectException::class);

        $controller->delete();
    }

    public function testUpdateFailsWhenPasswordInvalid(): void
    {
        $session = new FakeSession();
        $session->set('user_id', 1);

        $users = new FakeUserRepository();
        $users->findByIdResult = new User(1, 'name', 'test@example.com', password_hash('correct', PASSWORD_DEFAULT));

        $controller = $this->createController(
            request: new FakeRequest(post: [
                'token' => 'token',
                'name' => 'name',
                'mail' => 'test@example.com',
                'pass_current' => 'wrong'
            ], method: 'POST'),
            session: $session,
            users: $users
        );

        $this->expectException(RedirectException::class);

        try {
            $controller->mypage();
        } finally {
            $this->assertSame(UserController::ERROR_PASSWORD, $session->error());
        }
    }

    private function createController(
        ?FakeRequest $request = null,
        ?FakeSession $session = null,
        ?FakeResponse $response = null,
        ?FakeCsrf $csrf = null,
        ?FakeUserRepository $users = null,
        ?FakeLoginThrottle $throttle = null
    ): UserController {
        return new UserController(
            $request ?? new FakeRequest(),
            $session ?? new FakeSession(),
            $response ?? new FakeResponse(),
            $csrf ?? new FakeCsrf(),
            $users ?? new FakeUserRepository(),
            $throttle ?? new FakeLoginThrottle()
        );
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

    private function invalidSigninData(): array
    {
        return [
            'token' => 'token',
            'mail' => 'test@example.com',
            'pass' => 'wrong',
        ];
    }
}
