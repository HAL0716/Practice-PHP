<?php

declare(strict_types=1);

namespace App\Controllers;

final class HomeController extends \App\Core\Controller
{
    private const ERROR_SYSTEM = '処理に失敗しました。時間をおいて再度お試しください';

    public function index(): void
    {
        $this->requireLogin();

        if (\App\Core\Request::isGet()) {
            $this->render(
                'home',
                [
                    'title' => 'ホーム',
                    'token' => \App\Core\Csrf::token(),
                    'error' => \App\Core\Session::error(),
                    'old'   => \App\Core\Session::old(),
                    'posts' => \App\Models\PostRepository::findAll(),
                ]
            );
            return;
        }

        if (!\App\Core\Request::isPost()) {
            $this->redirectSelf(self::ERROR_SYSTEM);
        }

        $form = new \App\Forms\PostForm();

        if ($error = $this->checkCsrf($form->token())) {
            $this->redirectSelf($error, $form->old());
        }

        if ($error = $form->validate()) {
            $this->redirectSelf($error, $form->old());
        }

        $userId  = \App\Core\Session::userId();

        if (!\App\Models\PostRepository::create($userId, $form->comment())) {
            $this->redirectSelf(self::ERROR_SYSTEM, $form->old());
        }

        $this->redirect(\App\Constants\Routes::HOME);
    }
}
