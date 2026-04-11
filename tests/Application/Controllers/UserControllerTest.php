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
    private FakeSession $session;
    private FakeUserRepository $users;
    private FakeCsrf $csrf;
    private FakeLoginThrottle $throttle;

    protected function setUp(): void
    {
        $this->session = new FakeSession();
        $this->users = new FakeUserRepository();
        $this->csrf = new FakeCsrf();
        $this->throttle = new FakeLoginThrottle();
    }

    public function testSignupFailsWhenEmailAlreadyExists(): void
    {
        $this->users->findByEmailResult = $this->createUser();

        $controller = $this->createController([
            'request' => $this->signupRequest()
        ]);

        $response = $controller->signup();

        $this->assertSame(UserController::ERROR_EXISTS, $this->session->error());
        $this->assertSame('/user/signup', $response->getHeader('Location'));
    }

    public function testSignupFailsWhenCreateFails(): void
    {
        $this->users->createResult = null;

        $controller = $this->createController([
            'request' => $this->signupRequest()
        ]);

        $response = $controller->signup();

        $this->assertSame(UserController::ERROR_SYSTEM, $this->session->error());
        $this->assertSame('/user/signup', $response->getHeader('Location'));
    }

    public function testSigninFailsWithInvalidPassword(): void
    {
        $this->users->findByEmailResult = $this->createUser();

        $controller = $this->createController([
            'request' => $this->signinRequest('wrong')
        ]);

        $response = $controller->signin();

        $this->assertSame(UserController::ERROR_LOGIN, $this->session->error());
        $this->assertSame('/user/signin', $response->getHeader('Location'));
    }

    public function testSigninFailsWhenLocked(): void
    {
        $this->throttle->hitResult = 'locked';

        $controller = $this->createController([
            'request' => $this->signinRequest()
        ]);

        $response = $controller->signin();

        $this->assertSame($this->throttle->hitResult, $this->session->error());
        $this->assertSame('/user/signin', $response->getHeader('Location'));
    }

    public function testSignoutWhenNotLoggedIn(): void
    {
        $controller = $this->createController([
            'request' => new FakeRequest(method: 'GET')
        ]);

        $response = $controller->signout();

        $this->assertFalse($this->session->isLoggedIn());
        $this->assertSame('/user/signin', $response->getHeader('Location'));
    }

    public function testUpdateFailsWhenPasswordInvalid(): void
    {
        $this->session->set('user_id', 1);

        $this->users->findByIdResult = $this->createUser();

        $controller = $this->createController([
            'request' => new FakeRequest(
                post: [
                    'token' => 'token',
                    'name' => 'テストユーザー2',
                    'mail' => 'test@example.com',
                    'pass_current' => 'wrong'
                ],
                method: 'POST',
                path: '/user/mypage'
            )
        ]);

        $response = $controller->mypage();

        $this->assertSame(UserController::ERROR_PASSWORD, $this->session->error());
        $this->assertSame('/user/mypage', $response->getHeader('Location'));
    }

    private function createUser(): User
    {
        return new User(1, 'テストユーザー', 'test@example.com', password_hash('pass1234', PASSWORD_DEFAULT));
    }

    private function createController(array $override = []): UserController
    {
        return new UserController(
            $override['request'] ?? new FakeRequest(),
            $this->session,
            $this->csrf,
            $this->users,
            $this->throttle
        );
    }

    private function signupRequest(): FakeRequest
    {
        return new FakeRequest(
            post: [
                'token' => 'token',
                'name' => 'name',
                'mail' => 'test@example.com',
                'pass' => 'pass1234',
                'pass_confirm' => 'pass1234',
            ],
            method: 'POST',
            path: '/user/signup'
        );
    }

    private function signinRequest(string $pass = 'pass1234'): FakeRequest
    {
        return new FakeRequest(
            post: [
                'token' => 'token',
                'mail' => 'test@example.com',
                'pass' => $pass,
            ],
            method: 'POST',
            path: '/user/signin'
        );
    }
}
