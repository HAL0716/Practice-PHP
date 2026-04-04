<?php

declare(strict_types=1);

namespace Tests\Core\Http;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use App\Core\Http\Controller;
use App\Constants\Routes;
use Tests\Fake\Http\FakeRequest;
use Tests\Fake\Http\FakeSession;
use Tests\Fake\Http\FakeResponse;
use Tests\Fake\Security\FakeCsrf;

#[CoversClass(Controller::class)]
final class ControllerTest extends TestCase
{
    private FakeRequest $request;
    private FakeSession $session;
    private FakeResponse $response;
    private FakeCsrf $csrf;
    private TestController $controller;

    protected function setUp(): void
    {
        $this->request = new FakeRequest();
        $this->session = new FakeSession();
        $this->response = new FakeResponse();
        $this->csrf = new FakeCsrf();

        $this->controller = $this->newController();
    }

    public function testDispatchPost(): void
    {
        $this->setMethod('POST');

        $called = false;

        $this->controller->dispatchTest(
            post: function () use (&$called) {
                $called = true;
            }
        );

        $this->assertTrue($called);
    }

    public function testDispatchGet(): void
    {
        $this->setMethod('GET');

        $called = false;

        $this->controller->dispatchTest(
            get: function () use (&$called) {
                $called = true;
            }
        );

        $this->assertTrue($called);
    }

    public function testDispatchFallback(): void
    {
        $this->request = new FakeRequest([], [], 'PUT', '/current');
        $this->controller = $this->newController();

        $this->controller->dispatchTest();

        $this->assertSame('/current', $this->response->redirectTo);
        $this->assertSame(Controller::ERROR_SYSTEM, $this->session->error());
    }

    public function testRequireLoginRedirects(): void
    {
        $this->controller->requireLoginTest();

        $this->assertSame(Routes::USER_SIGNIN, $this->response->redirectTo);
    }

    public function testRequireLoginPasses(): void
    {
        $this->session->set('user_id', 1);

        $this->controller->requireLoginTest();

        $this->assertNull($this->response->redirectTo);
    }

    public function testRedirect(): void
    {
        $this->controller->redirectTest('/test', 'error', ['a' => 1]);

        $this->assertSame('/test', $this->response->redirectTo);
        $this->assertSame('error', $this->session->error());
        $this->assertSame(['a' => 1], $this->session->old());
    }

    public function testRedirectSelf(): void
    {
        $this->request = new FakeRequest([], [], 'GET', '/self');
        $this->controller = $this->newController();

        $this->controller->redirectSelfTest('error');

        $this->assertSame('/self', $this->response->redirectTo);
    }

    public function testCheckCsrf(): void
    {
        $this->assertNull($this->controller->checkCsrfTest('token'));

        $this->csrf = new FakeCsrf();
        $this->controller = $this->newController();

        $this->assertSame(Controller::ERROR_CSRF, $this->controller->checkCsrfTest('TOKEN'));
    }

    public function testUserId(): void
    {
        $this->session->set('user_id', 5);

        $this->assertSame(5, $this->controller->userIdTest());
    }

    private function setMethod(string $method): void
    {
        $this->request = new FakeRequest([], [], $method);
        $this->controller = $this->newController();
    }

    private function newController(): TestController
    {
        return new TestController($this->request, $this->session, $this->response, $this->csrf);
    }
}
