<?php

declare(strict_types=1);

namespace Tests\Integration\Auth;

use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
final class AuthGuardTest extends AuthTestCase
{
    public function testUserMypageRequiresLogin(): void
    {
        $this->assertGuest();

        $response = $this->get('/user/mypage');

        $this->assertRedirect($response, self::SIGNIN_URL);
    }

    public function testPostHomeRequiresLogin(): void
    {
        $this->assertGuest();

        $response = $this->get('/post/home');

        $this->assertRedirect($response, self::SIGNIN_URL);
    }

    public function testPostCreateRequiresLogin(): void
    {
        $this->assertGuest();

        $response = $this->post('/post/home', [
            'token' => 'dummy',
            'comment' => 'hello'
        ]);

        $this->assertRedirect($response, self::SIGNIN_URL);
    }

    public function testPostDeleteRequiresLogin(): void
    {
        $this->assertGuest();

        $response = $this->post('/post/delete', [
            'token' => 'dummy',
            'id' => 1
        ]);

        $this->assertRedirect($response, self::SIGNIN_URL);
    }
}
