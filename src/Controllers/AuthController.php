<?php

declare(strict_types=1);

namespace App\Controllers;

class AuthController extends \App\Core\Controller
{
    private const DUMMY_HASH = '$2y$10$wH3Gm1H4qJ5FQGqV3y4kUe1xW8Vh3kQn6YbK7QeY8bJ2sD0m9F8aK';

    private const ERROR_PASSWORD = '現在のパスワードが正しくありません';
    private const ERROR_EXISTS   = 'このメールアドレスは既に登録されています';
    private const ERROR_LOGIN    = 'メールアドレスまたはパスワードが正しくありません';
    private const ERROR_LOCKED   = 'ログイン試行回数が上限に達しました。しばらくしてから再度お試しください';
    private const ERROR_SYSTEM   = '処理に失敗しました。時間をおいて再度お試しください';

    public function signup(): void
    {
        if (\App\Core\Request::isGet()) {
            $this->renderForm('auth/signup', 'サインアップ', \App\Constants\Routes::SIGNUP);
            return;
        }

        if (!\App\Core\Request::isPost()) {
            $this->redirectSelf(self::ERROR_SYSTEM);
        }

        $form = new \App\Forms\SignupForm();

        $this->checkCsrf($form->token());

        if ($error = $form->validate()) {
            $this->redirectSelf($error, $form->old());
        }

        if (\App\Models\UserRepository::findByEmail($form->mail())) {
            $this->redirectSelf(self::ERROR_EXISTS, $form->old());
        }

        $user = \App\Models\UserRepository::create(
            $form->name(),
            $form->mail(),
            $form->pass()
        );

        if (!$user) {
            $this->redirectSelf(self::ERROR_SYSTEM, $form->old());
        }

        \App\Core\Session::login($user);

        $this->redirect(\App\Constants\Routes::HOME);
    }

    public function signin(): void
    {
        if (\App\Core\Request::isGet()) {
            $this->renderForm('auth/signin', 'サインイン', \App\Constants\Routes::SIGNIN);
            return;
        }

        if (!\App\Core\Request::isPost()) {
            $this->redirectSelf(self::ERROR_SYSTEM);
        }

        if (\App\Core\LoginThrottle::isLocked()) {
            $this->redirectSelf(self::ERROR_LOCKED);
        }

        $form = new \App\Forms\SigninForm();

        $this->checkCsrf($form->token());

        if ($error = $form->validate()) {
            $this->redirectSelf($error, $form->old());
        }

        $user = \App\Models\UserRepository::findByEmail($form->mail());

        $valid = $user
            ? $user->verifyPassword($form->pass())
            : password_verify($form->pass(), self::DUMMY_HASH);

        if (!$valid) {

            if (\App\Core\LoginThrottle::hit()) {
                $this->redirectSelf(self::ERROR_LOCKED, $form->old());
            }

            $this->redirectSelf(self::ERROR_LOGIN, $form->old());
        }

        \App\Core\LoginThrottle::clear();

        \App\Core\Session::login($user);

        $this->redirect(\App\Constants\Routes::HOME);
    }

    public function signout(): void
    {
        if (!\App\Core\Request::isPost()) {
            $this->redirectSelf(self::ERROR_SYSTEM);
        }

        $this->requireLogin();

        \App\Core\Session::logout();

        $this->redirect(\App\Constants\Routes::SIGNIN);
    }

    public function mypage(): void
    {
        $this->requireLogin();

        if (\App\Core\Request::isGet()) {
            $user = \App\Models\UserRepository::findById(\App\Core\Session::userId());

            if (!$user) {
                \App\Core\Session::logout();
                $this->redirect(\App\Constants\Routes::SIGNIN);
            }

            $this->render('auth/mypage', [
                'title'     => 'マイページ',
                'token'     => \App\Core\Csrf::token(),
                'error'     => \App\Core\Session::error(),
                'old'       => \App\Core\Session::old(),
                'user'      => $user,
                'actionUrl' => \App\Constants\Routes::MYPAGE,
            ]);
            return;
        }

        if (!\App\Core\Request::isPost()) {
            $this->redirectSelf(self::ERROR_SYSTEM);
        }

        $form = new \App\Forms\MypageForm();

        $this->checkCsrf($form->token());

        if ($error = $form->validate()) {
            $this->redirectSelf($error, $form->old());
        }

        $userId = \App\Core\Session::userId();
        $user   = \App\Models\UserRepository::findById($userId);

        if (!$user || !$user->verifyPassword($form->passCurrent())) {
            $this->redirectSelf(self::ERROR_PASSWORD, $form->old());
        }

        if (!\App\Models\UserRepository::update(
            $userId,
            $form->name() === '' ? null : $form->name(),
            $form->mail() === '' ? null : $form->mail(),
            $form->pass() === '' ? null : $form->pass()
        )) {
            $this->redirectSelf(self::ERROR_SYSTEM, $form->old());
        }

        $this->redirectSelf();
    }

    private function renderForm(string $view, string $title, string $action): void
    {
        $this->render($view, [
            'title'     => $title,
            'token'     => \App\Core\Csrf::token(),
            'error'     => \App\Core\Session::error(),
            'old'       => \App\Core\Session::old(),
            'actionUrl' => $action,
        ]);
    }
}
