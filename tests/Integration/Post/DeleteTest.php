<?php

declare(strict_types=1);

namespace Tests\Integration\Post;

use App\Application\Controllers\PostController;
use App\Application\Http\ResponseInterface;
use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
final class DeleteTest extends PostTestCase
{
    public function testDeleteSuccess(): void
    {
        $this->createUser();
        $this->login($this->users()->findByEmail(self::DEFAULT_EMAIL));

        $post = $this->createPost();

        $before = count($this->posts()->findAll());

        $response = $this->postDelete([
            'id' => $post->id(),
        ]);

        $after = count($this->posts()->findAll());
        $this->assertSame($before - 1, $after);

        $this->assertNull($this->posts()->findById($post->id()));
        $this->assertRedirect($response, self::HOME_URL);
    }

    public function testDeleteWithoutToken(): void
    {
        $this->createUser();
        $this->login($this->users()->findByEmail(self::DEFAULT_EMAIL));

        $post = $this->createPost();

        $before = count($this->posts()->findAll());

        $response = $this->postDelete([
            'token' => null,
            'id' => $post->id(),
        ]);

        $after = count($this->posts()->findAll());
        $this->assertSame($before, $after);

        $this->assertNotNull($this->posts()->findById($post->id()));
        $this->assertError(PostController::ERROR_CSRF);
        $this->assertRedirect($response, self::HOME_URL);
    }

    public function testDeleteInvalidToken(): void
    {
        $this->createUser();
        $this->login($this->users()->findByEmail(self::DEFAULT_EMAIL));

        $post = $this->createPost();

        $before = count($this->posts()->findAll());

        $response = $this->postDelete([
            'token' => 'invalid-token',
            'id' => $post->id(),
        ]);

        $after = count($this->posts()->findAll());
        $this->assertSame($before, $after);

        $this->assertNotNull($this->posts()->findById($post->id()));
        $this->assertError(PostController::ERROR_CSRF);
        $this->assertRedirect($response, self::HOME_URL);
    }

    public function testGuestCannotDeletePost(): void
    {
        $this->createUser();
        $user = $this->users()->findByEmail(self::DEFAULT_EMAIL);

        $post = $this->posts()->create($user->id(), self::DEFAULT_COMMENT);

        $before = count($this->posts()->findAll());

        $response = $this->postDelete([
            'id' => $post->id(),
        ]);

        $after = count($this->posts()->findAll());
        $this->assertSame($before, $after);

        $this->assertNotNull($this->posts()->findById($post->id()));
        $this->assertRedirect($response, self::SIGNIN_URL);
    }

    public function testCannotDeleteOtherUsersPost(): void
    {
        // user1
        $this->createUser();
        $user1 = $this->users()->findByEmail(self::DEFAULT_EMAIL);
        $this->login($user1);

        $post = $this->posts()->create($user1->id(), self::DEFAULT_COMMENT);

        // user2
        $this->users()->create('Other', 'other@example.com', 'password123');
        $user2 = $this->users()->findByEmail('other@example.com');
        $this->login($user2);

        $before = count($this->posts()->findAll());

        $response = $this->postDelete([
            'id' => $post->id(),
        ]);

        $after = count($this->posts()->findAll());
        $this->assertSame($before, $after);

        $this->assertNotNull($this->posts()->findById($post->id()));
        $this->assertRedirect($response, self::HOME_URL);
    }

    private function postDelete(array $override = []): ResponseInterface
    {
        return $this->getResponse(
            'POST',
            self::DELETE_URL,
            array_merge([
                'token' => $this->csrfToken(),
            ], $override)
        );
    }
}
