<?php

declare(strict_types=1);

namespace Tests\Integration\User;

use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
final class SignoutTest extends UserTestCase
{
    public function testSignoutSuccess(): void
    {
        $this->createUser();
        $this->login($this->users()->findByEmail(self::DEFAULT_EMAIL));

        $response = $this->getResponse('GET', self::SIGNOUT_URL);

        $this->assertGuest();
        $this->assertRedirect($response, self::SIGNIN_URL);
    }

    public function testSignoutWhenGuest(): void
    {
        $this->assertGuest();

        $response = $this->getResponse('GET', self::SIGNOUT_URL);

        $this->assertGuest();
        $this->assertRedirect($response, self::SIGNIN_URL);
    }
}
