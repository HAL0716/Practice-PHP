<?php

declare(strict_types=1);

namespace Tests\Application\Controllers;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use App\Application\Controllers\UserController;
use App\Domain\User\User;
use Tests\Fake\Domain\FakeUserRepository;
use Tests\Fake\Infrastructure\Http\FakeRequest;
use Tests\Fake\Infrastructure\Http\FakeSession;
use Tests\Fake\Infrastructure\Security\FakeCsrf;
use Tests\Fake\Infrastructure\Security\FakeLoginThrottle;

#[CoversClass(UserController::class)]
final class UserControllerTest extends TestCase
{
    public function testSignupSuccess(): void
    {
        $session = new FakeSession();
        $users = new FakeUserRepository();

        $users->createResult = new User(1, 'name', 'test@example.com', 'hash');

        $controller = $this->createController(
            request: new FakeRequest(post: $this->validSignupData(), method: 'POST'),
            session: $session,
            users: $users
        );

        $response = $controller->signup();

        $this->assertTrue($session->isLoggedIn());
        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('/post/home', $response->getHeader('Location'));
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

        $response = $controller->signup();

        $this->assertSame(UserController::ERROR_EXISTS, $session->error());
        $this->assertSame(302, $response->getStatusCode());
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

        $response = $controller->signup();

        $this->assertSame(UserController::ERROR_SYSTEM, $session->error());
        $this->assertSame(302, $response->getStatusCode());
    }

    public function testSigninSuccess(): void
    {
        $session = new FakeSession();
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
            users: $users,
            throttle: $throttle
        );

        $response = $controller->signin();

        $this->assertTrue($session->isLoggedIn());
        $this->assertTrue($throttle->cleared);
        $this->assertSame('/post/home', $response->getHeader('Location'));
    }

    public function testSigninFails(): void
    {
        $session = new FakeSession();
        $users = new FakeUserRepository();

        $controller = $this->createController(
            request: new FakeRequest(
                post: $this->invalidSigninData(),
                method: 'POST',
                path: '/user/signin'
            ),
            session: $session,
            users: $users
        );

        $response = $controller->signin();

        $this->assertSame(UserController::ERROR_LOGIN, $session->error());
        $this->assertSame('/user/signin', $response->getHeader('Location'));
    }

    public function testSigninLocked(): void
    {
        $session = new FakeSession();
        $throttle = new FakeLoginThrottle();
        $throttle->hitResult = 'locked';

        $controller = $this->createController(
            request: new FakeRequest(
                post: $this->invalidSigninData(),
                method: 'POST',
                path: '/user/signin'
            ),
            session: $session,
            throttle: $throttle
        );

        $response = $controller->signin();

        $this->assertSame('locked', $session->error());
        $this->assertSame('/user/signin', $response->getHeader('Location'));
    }

    public function testSignout(): void
    {
        $session = new FakeSession();
        $session->set('user_id', 1);

        $controller = $this->createController(
            request: new FakeRequest(method: 'GET'),
            session: $session
        );

        $response = $controller->signout();

        $this->assertFalse($session->isLoggedIn());
        $this->assertSame('/user/signin', $response->getHeader('Location'));
    }

    public function testDeleteSuccess(): void
    {
        $session = new FakeSession();
        $session->set('user_id', 1);

        $users = new FakeUserRepository();
        $users->deleteResult = true;
        $users->findByIdResult = new User(
            1,
            'name',
            'test@example.com',
            password_hash('pass1234', PASSWORD_DEFAULT)
        );

        $controller = $this->createController(
            request: new FakeRequest(
                post: [
                    'token' => 'token',
                    'pass_current' => 'pass1234'
                ],
                method: 'POST',
                path: '/user/delete'
            ),
            session: $session,
            users: $users
        );

        $response = $controller->delete();

        $this->assertSame('/user/signin', $response->getHeader('Location'));
    }

    public function testUpdateFailsWhenPasswordInvalid(): void
    {
        $session = new FakeSession();
        $session->set('user_id', 1);

        $users = new FakeUserRepository();
        $users->findByIdResult = new User(
            1,
            'name',
            'test@example.com',
            password_hash('correct', PASSWORD_DEFAULT)
        );

        $controller = $this->createController(
            request: new FakeRequest(
                post: [
                    'token' => 'token',
                    'name' => 'name',
                    'mail' => 'test@example.com',
                    'pass_current' => 'wrong'
                ],
                method: 'POST',
                path: '/user/mypage'
            ),
            session: $session,
            users: $users
        );

        $response = $controller->mypage();

        $this->assertSame(UserController::ERROR_PASSWORD, $session->error());
        $this->assertSame('/user/mypage', $response->getHeader('Location'));
    }

    private function createController(
        ?FakeRequest $request = null,
        ?FakeSession $session = null,
        ?FakeCsrf $csrf = null,
        ?FakeUserRepository $users = null,
        ?FakeLoginThrottle $throttle = null
    ): UserController {
        return new UserController(
            $request ?? new FakeRequest(),
            $session ?? new FakeSession(),
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
