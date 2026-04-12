<?php

declare(strict_types=1);

namespace Tests\Integration\Auth;

use PHPUnit\Framework\Attributes\CoversNothing;
use Tests\Integration\IntegrationTestCase;

#[CoversNothing]
final class AuthGuardTest extends IntegrationTestCase
{
    public function testUserMypageRequiresLogin(): void
    {
        $response = $this->getResponse('GET', '/user/mypage');

        $this->assertRedirect($response, '/user/signin');
    }

    public function testPostHomeRequiresLogin(): void
    {
        $response = $this->getResponse('GET', '/post/home');

        $this->assertRedirect($response, '/user/signin');
    }

    public function testPostCreateRequiresLogin(): void
    {
        $response = $this->getResponse('POST', '/post/home', [
            'token' => 'dummy',
            'comment' => 'hello'
        ]);

        $this->assertRedirect($response, '/user/signin');
    }

    public function testPostDeleteRequiresLogin(): void
    {
        $response = $this->getResponse('POST', '/post/delete', [
            'token' => 'dummy',
            'id' => 1
        ]);

        $this->assertRedirect($response, '/user/signin');
    }
}
