<?php

declare(strict_types=1);

namespace Tests\Integration\Post;

use App\Domain\Post\Post;
use Tests\Integration\IntegrationTestCase;

abstract class PostTestCase extends IntegrationTestCase
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
}
