<?php

declare(strict_types=1);

namespace Tests\Integration\Post;

use App\Application\Controllers\PostController;
use App\Application\Http\ResponseInterface;
use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
final class CreateTest extends PostTestCase
{
    public function testCreateSuccess(): void
    {
        $this->createUser();
        $this->login($this->users()->findByEmail(self::DEFAULT_EMAIL));

        $beforePosts = $this->posts()->findAll();
        $beforeCount = count($beforePosts);

        $response = $this->postCreate();

        $afterPosts = $this->posts()->findAll();
        $afterCount = count($afterPosts);

        $this->assertSame($beforeCount + 1, $afterCount);

        $newPost = $this->findNewPost($beforePosts, $afterPosts);

        $this->assertSame(self::DEFAULT_COMMENT, $newPost->comment());

        $this->assertRedirect($response, self::HOME_URL);
    }

    public function testCreateWithoutToken(): void
    {
        $this->createUser();
        $this->login($this->users()->findByEmail(self::DEFAULT_EMAIL));

        $before = count($this->posts()->findAll());

        $response = $this->postCreate(['token' => null]);

        $after = count($this->posts()->findAll());
        $this->assertSame($before, $after);

        $this->assertError(PostController::ERROR_CSRF);
        $this->assertRedirect($response, self::HOME_URL);
    }

    public function testCreateInvalidToken(): void
    {
        $this->createUser();
        $this->login($this->users()->findByEmail(self::DEFAULT_EMAIL));

        $before = count($this->posts()->findAll());

        $response = $this->postCreate(['token' => 'invalid-token']);

        $after = count($this->posts()->findAll());
        $this->assertSame($before, $after);

        $this->assertError(PostController::ERROR_CSRF);
        $this->assertRedirect($response, self::HOME_URL);
    }

    public function testGuestCannotCreatePost(): void
    {
        $before = count($this->posts()->findAll());

        $response = $this->postCreate();

        $after = count($this->posts()->findAll());
        $this->assertSame($before, $after);

        $this->assertRedirect($response, self::SIGNIN_URL);
    }

    private function findNewPost(array $before, array $after)
    {
        $beforeIds = array_map(fn($p) => $p->id(), $before);

        foreach ($after as $post) {
            if (!in_array($post->id(), $beforeIds, true)) {
                return $post;
            }
        }

        $this->fail('New post was not found.');
    }

    private function postCreate(array $override = []): ResponseInterface
    {
        return $this->getResponse(
            'POST',
            self::HOME_URL,
            array_merge([
                'token' => $this->csrfToken(),
                'comment' => self::DEFAULT_COMMENT,
            ], $override)
        );
    }
}
