<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Http\Controller;
use App\Forms\PostForm;
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
}
