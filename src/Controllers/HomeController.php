<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Constants\Routes;
use App\Core\Http\Controller;
use App\Core\Http\Request;
use App\Core\Http\Session;
use App\Core\Security\Csrf;
use App\Forms\PostForm;
use App\Models\PostRepository;

final class HomeController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();

        if (Request::isGet()) {
            $this->render(
                'home',
                [
                    'title'   => 'ホーム',
                    'token'   => Csrf::token(),
                    'error'   => Session::error(),
                    'old'     => Session::old(),
                    'user_id' => Session::userId(),
                    'posts'   => PostRepository::findAll(),
                ]
            );
            return;
        }

        if (Request::isPost()) {
            $this->indexPost();
            return;
        }

        $this->redirectSelf(self::ERROR_SYSTEM);
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
