<?php

declare(strict_types=1);

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/PostRepository.php';

class HomeController extends Controller
{
    private const ERROR_SYSTEM = '処理に失敗しました。時間をおいて再度お試しください';

    public function index(): void
    {
        $this->requireLogin();

        if (Request::isGet()) {
            $this->render(
                'home',
                [
                    'title' => 'ホーム',
                    'token' => Csrf::token(),
                    'error' => Session::getFlash(SessionKeys::ERRORS),
                    'old'   => Session::getFlash(SessionKeys::OLD),
                    'posts' => PostRepository::findAll(),
                ]
            );
        }

        $form = new PostForm();

        $this->checkCsrf($form->token());

        if ($error = $form->validate()) {
            $this->redirectSelf($error, $form->old());
        }

        $userId  = Session::userId();

        if (!PostRepository::create($userId, $form->comment())) {
            $this->redirectSelf(self::ERROR_SYSTEM, $form->old());
        }

        $this->redirect(Routes::HOME);
    }
}
