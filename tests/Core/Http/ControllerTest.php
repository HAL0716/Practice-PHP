<?php

declare(strict_types=1);

namespace Tests\Core\Http;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use App\Application\Http\Controller;
use App\Constants\Routes;
use Tests\Fake\Http\FakeRequest;
use Tests\Fake\Http\FakeSession;
use Tests\Fake\Http\FakeResponse;
use Tests\Fake\Http\RedirectException;
use Tests\Fake\Security\FakeCsrf;
use Tests\Fake\Form\FakeForm;

#[CoversClass(Controller::class)]
final class ControllerTest extends TestCase
{
    public function testDispatchPost(): void
    {
        $request = new FakeRequest([], [], 'POST');
        $controller = $this->createController(request: $request);

        $called = false;

        $controller->dispatchTest(
            post: function () use (&$called) {
                $called = true;
            }
        );

        $this->assertTrue($called);
    }

    public function testDispatchGet(): void
    {
        $request = new FakeRequest([], [], 'GET');
        $controller = $this->createController(request: $request);

        $called = false;

        $controller->dispatchTest(
            get: function () use (&$called) {
                $called = true;
            }
        );

        $this->assertTrue($called);
    }

    public function testDispatchFallback(): void
    {
        $request = new FakeRequest([], [], 'PUT', '/current');
        $response = new FakeResponse();
        $session = new FakeSession();
        $controller = $this->createController(request: $request, response: $response, session: $session);

        $this->expectException(RedirectException::class);

        try {
            $controller->dispatchTest();
        } finally {
            $this->assertSame('/current', $response->redirectTo);
            $this->assertSame(Controller::ERROR_SYSTEM, $session->error());
        }
    }

    public function testRequireLoginRedirects(): void
    {
        $response = new FakeResponse();
        $controller = $this->createController(response: $response);

        $this->expectException(RedirectException::class);

        try {
            $controller->requireLoginTest();
        } finally {
            $this->assertSame(Routes::USER_SIGNIN, $response->redirectTo);
        }
    }

    public function testRequireLoginPasses(): void
    {
        $session = new FakeSession();
        $session->set('user_id', 1);
        $controller = $this->createController(session: $session);

        $controller->requireLoginTest();

        $this->assertNull($session->error());
    }

    public function testRedirect(): void
    {
        $response = new FakeResponse();
        $session = new FakeSession();
        $controller = $this->createController(response: $response, session: $session);

        $this->expectException(RedirectException::class);

        try {
            $controller->redirectTest('/test', 'error', ['a' => 1]);
        } finally {
            $this->assertSame('/test', $response->redirectTo);
            $this->assertSame('error', $session->error());
            $this->assertSame(['a' => 1], $session->old());
        }
    }

    public function testRedirectSelf(): void
    {
        $request = new FakeRequest([], [], 'GET', '/self');
        $response = new FakeResponse();
        $controller = $this->createController(request: $request, response: $response);

        $this->expectException(RedirectException::class);

        try {
            $controller->redirectSelfTest('error');
        } finally {
            $this->assertSame('/self', $response->redirectTo);
        }
    }

    public function testCheckCsrfSuccess(): void
    {
        $controller = $this->createController();
        $this->assertNull($controller->checkCsrfTest('token'));
    }

    public function testCheckCsrfFail(): void
    {
        $controller = $this->createController();

        $this->assertSame(Controller::ERROR_CSRF, $controller->checkCsrfTest('invalid'));
    }

    public function testEnsureValidFormSuccess(): void
    {
        $request = new FakeRequest(['token' => 'token']);
        $form = new FakeForm($request, valid: true);
        $response = new FakeResponse();

        $controller = $this->createController(request: $request, response: $response);

        $controller->ensureValidFormTest($form);

        $this->assertNull($response->redirectTo);
    }

    public function testEnsureValidFormFail(): void
    {
        $request = new FakeRequest(['token' => 'token']);
        $form = new FakeForm($request, valid: false);
        $response = new FakeResponse();
        $controller = $this->createController(request: $request, response: $response);

        $this->expectException(RedirectException::class);

        try {
            $controller->ensureValidFormTest($form);
        } finally {
            $this->assertSame('/', $response->redirectTo);
        }
    }

    public function testUserId(): void
    {
        $session = new FakeSession();
        $session->set('user_id', 5);
        $controller = $this->createController(session: $session);

        $this->assertSame(5, $controller->userIdTest());
    }

    private function createController(?FakeRequest $request = null, ?FakeSession $session = null, ?FakeResponse $response = null, ?FakeCsrf $csrf = null): TestController
    {
        $request ??= new FakeRequest();
        $session ??= new FakeSession();
        $response ??= new FakeResponse();
        $csrf ??= new FakeCsrf();

        return new TestController($request, $session, $response, $csrf);
    }
}
