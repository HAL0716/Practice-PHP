<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Http\Controller;
use App\Core\Http\Session;
use App\Core\Security\Csrf;
use App\Forms\PostForm;
use App\Models\PostRepository;

final class HomeController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();

        $this->dispatch(
            get: fn () => $this->indexGet(),
            post: fn () => $this->indexPost()
        );
    }

    private function indexGet(): void
    {
        $this->render(
            'home',
            [
                'user_id' => Session::userId(),
                'posts'   => PostRepository::findAll(),
            ]
        );
    }

    private function indexPost(): void
    {
        $form = new PostForm();

        if (!$this->ensureValidForm($form)) {
            return;
        }

        $userId = Session::userId();

        if (!PostRepository::create($userId, $form->comment())) {
            $this->redirectSelf(self::ERROR_SYSTEM, $form->old());
        }

        $this->redirectSelf();
    }
}
