<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Constants\Routes;
use App\Core\Http\Controller;
use App\Domain\Post\PostRepository;
use App\Forms\Post\CreateForm;
use App\Forms\Post\DeleteForm;

final class PostController extends Controller
{
    public function home(): void
    {
        $this->requireLogin();

        $this->dispatch(
            get: fn () => $this->showPosts(),
            post: fn () => $this->createPost()
        );
    }

    public function delete(): void
    {
        $this->requireLogin();

        $this->dispatch(
            post: fn () => $this->deletePost()
        );
    }

    private function showPosts(): void
    {
        $this->render('post/home', [
            'user_id' => $this->userId(),
            'posts'   => PostRepository::findAll(),
        ]);
    }

    private function createPost(): void
    {
        $form = new CreateForm();

        if (!$this->ensureValidForm($form)) {
            return;
        }

        if (!PostRepository::create($this->userId(), $form->comment())) {
            $this->redirectSelf(self::ERROR_SYSTEM, $form->old());
        }

        $this->redirectSelf();
    }

    private function deletePost(): void
    {
        $form = new DeleteForm();

        if (!$this->ensureValidForm($form)) {
            return;
        }

        if (!PostRepository::delete($form->id(), $this->userId())) {
            $this->redirect(Routes::HOME, self::ERROR_SYSTEM);
        }

        $this->redirect(Routes::HOME);
    }
}
