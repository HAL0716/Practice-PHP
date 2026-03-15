<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Constants\Routes;
use App\Core\Http\Controller;
use App\Forms\PostForm;
use App\Forms\DeletePostForm;
use App\Models\PostRepository;

final class HomeController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();

        $this->dispatch(
            get: fn () => $this->render('home', ['user_id' => $this->userId(), 'posts' => PostRepository::findAll()]),
            post: fn () => $this->indexPost()
        );
    }

    private function indexPost(): void
    {
        $form = new PostForm();

        if (!$this->ensureValidForm($form)) {
            return;
        }

        if (!PostRepository::create($this->userId(), $form->comment())) {
            $this->redirectSelf(self::ERROR_SYSTEM, $form->old());
        }

        $this->redirectSelf();
    }

    public function delete(): void
    {
        $this->requireLogin();

        $this->dispatch(
            post: fn () => $this->deletePost()
        );
    }

    public function deletePost(): void
    {
        $form = new DeletePostForm();

        if (!$this->ensureValidForm($form)) {
            return;
        }

        if (!PostRepository::delete($form->id())) {
            $this->redirect(Routes::HOME, self::ERROR_SYSTEM);
        }

        $this->redirect(Routes::HOME);
    }
}
