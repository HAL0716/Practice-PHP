<?php

declare(strict_types=1);

namespace Tests\Integration\Post;

use PHPUnit\Framework\Attributes\CoversNothing;
use App\Application\Controllers\PostController;

#[CoversNothing]
final class CreateTest extends PostTestCase
{
    public function testCreateSuccess(): void
    {
        $this->loginAsUser();

        $before = $this->posts()->findAll();

        $response = $this->postCreate();

        $after = $this->posts()->findAll();

        $this->assertRedirect($response, self::HOME_URL);
        $this->assertPostCreated($before, $after);
    }

    public function testCreateWithoutToken(): void
    {
        $this->loginAsUser();

        $before = $this->posts()->findAll();

        $response = $this->postCreate(['token' => null]);

        $after = $this->posts()->findAll();

        $this->assertError(PostController::ERROR_CSRF);
        $this->assertRedirect($response, self::HOME_URL);
        $this->assertPostNotCreated($before, $after);
    }

    public function testCreateInvalidToken(): void
    {
        $this->loginAsUser();

        $before = $this->posts()->findAll();

        $response = $this->postCreate(['token' => 'invalid-token']);

        $after = $this->posts()->findAll();

        $this->assertError(PostController::ERROR_CSRF);
        $this->assertRedirect($response, self::HOME_URL);
        $this->assertPostNotCreated($before, $after);
    }

    public function testGuestCannotCreatePost(): void
    {
        $this->assertGuest();

        $before = $this->posts()->findAll();

        $response = $this->postCreate();

        $after = $this->posts()->findAll();

        $this->assertRedirect($response, self::SIGNIN_URL);
        $this->assertPostNotCreated($before, $after);
    }

    // =========================
    // DB Assertions
    // =========================

    private function assertPostCreated(array $before, array $after): void
    {
        $this->assertSame(count($before) + 1, count($after));

        $beforeIds = array_map(fn($p) => $p->id(), $before);
        $afterIds = array_map(fn($p) => $p->id(), $after);

        foreach ($afterIds as $id) {
            if (!in_array($id, $beforeIds, true)) {
                return;
            }
        }

        $this->fail('New post was not created in the database.');
    }

    private function assertPostNotCreated(array $before, array $after): void
    {
        $this->assertSame(count($before), count($after));
    }
}
