<?php

declare(strict_types=1);

namespace Tests\Application\Http;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use App\Application\Http\Controller;
use App\Application\Constants\RoutePaths;
use Tests\Fake\Infrastructure\Http\FakeRequest;
use Tests\Fake\Infrastructure\Http\FakeSession;
use Tests\Fake\Infrastructure\Security\FakeCsrf;
use Tests\Fake\Support\FakeForm;

#[CoversClass(Controller::class)]
final class ControllerTest extends TestCase
{
    public function testCheckCsrfSuccess(): void
    {
        $controller = $this->createController();

        $this->assertNull($controller->checkCsrfTest('token'));
    }

    public function testCheckCsrfFail(): void
    {
        $controller = $this->createController();

        $this->assertSame(
            Controller::ERROR_CSRF,
            $controller->checkCsrfTest('invalid')
        );
    }

    public function testEnsureValidFormSuccess(): void
    {
        $request = new FakeRequest(['token' => 'token']);
        $form = new FakeForm($request, valid: true);

        $controller = $this->createController(request: $request);

        $response = $controller->ensureValidFormTest($form);

        $this->assertNull($response);
    }

    public function testEnsureValidFormFail(): void
    {
        $request = new FakeRequest(['token' => 'token']);
        $form = new FakeForm($request, valid: false);

        $controller = $this->createController(request: $request);

        $response = $controller->ensureValidFormTest($form);

        $this->assertSame('/', $response->getHeader('Location'));
    }

    public function testUserId(): void
    {
        $session = new FakeSession();
        $session->set('user_id', 5);

        $controller = $this->createController(session: $session);

        $this->assertSame(5, $controller->userIdTest());
    }

    public function testUserIdRedirectsWhenNotLoggedIn(): void
    {
        $controller = $this->createController();

        $response = $controller->userIdTest();

        $this->assertSame(RoutePaths::USER_SIGNIN, $response->getHeader('Location'));
    }

    private function createController(
        ?FakeRequest $request = null,
        ?FakeSession $session = null,
        ?FakeCsrf $csrf = null
    ): TestController {
        return new TestController(
            $request ?? new FakeRequest(),
            $session ?? new FakeSession(),
            $csrf ?? new FakeCsrf()
        );
    }
}
