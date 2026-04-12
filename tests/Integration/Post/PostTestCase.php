<?php

declare(strict_types=1);

namespace Tests\Integration\Post;

use Tests\Integration\Auth\AuthTestCase;
use App\Application\Http\ResponseInterface;
use App\Domain\Post\Post;

abstract class PostTestCase extends AuthTestCase
{
    protected const DELETE_URL = '/post/delete';

    protected const DEFAULT_COMMENT = 'テスト投稿';

    protected function createPost(array $overrides = []): ?Post
    {
        $data = array_merge([
            'token' => $this->csrfToken(),
            'comment' => self::DEFAULT_COMMENT,
        ], $overrides);

        return $this->posts()->create($this->session()->get('user_id'), $data['comment']);
    }

    protected function postCreate(array $override = []): ResponseInterface
    {
        return $this->post(
            self::HOME_URL,
            array_merge([
                'token' => $this->csrfToken(),
                'comment' => self::DEFAULT_COMMENT,
            ], $override)
        );
    }

    protected function postDelete(array $override = []): ResponseInterface
    {
        return $this->post(
            self::DELETE_URL,
            array_merge([
                'token' => $this->csrfToken(),
            ], $override)
        );
    }
}
