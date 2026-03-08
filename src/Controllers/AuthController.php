<?php

declare(strict_types=1);

namespace App\Controllers;

final class AuthController extends \App\Core\Http\Controller
{
    private const DUMMY_HASH = '$2y$10$wH3Gm1H4qJ5FQGqV3y4kUe1xW8Vh3kQn6YbK7QeY8bJ2sD0m9F8aK';

    private const ERROR_PASSWORD = '現在のパスワードが正しくありません';
    private const ERROR_EXISTS   = 'このメールアドレスは既に登録されています';
    private const ERROR_LOGIN    = 'メールアドレスまたはパスワードが正しくありません';
    private const ERROR_LOCKED   = 'ログイン試行回数が上限に達しました。しばらくしてから再度お試しください';

    public function signup(): void
    {
        if (\App\Core\Http\Request::isPost()) {
            $this->signupPost();
            return;
        }

        if (\App\Core\Http\Request::isGet()) {
            $this->renderForm(
                'auth/signup',
                'サインアップ',
                \App\Constants\Routes::SIGNUP
            );
            return;
        }

        $this->redirectSelf(self::ERROR_SYSTEM);
    }

    private function signupPost(): void
    {
        $form = new \App\Forms\SignupForm();

        if ($error = $this->checkCsrf($form->token())) {
            $this->redirectSelf($error, $form->old());
        }

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

        \App\Core\Http\Session::login($user);

        $this->redirect(\App\Constants\Routes::HOME);
    }

    public function signin(): void
    {
        if (\App\Core\Http\Request::isPost()) {
            $this->signinPost();
            return;
        }

        if (\App\Core\Http\Request::isGet()) {
            $this->renderForm(
                'auth/signin',
                'サインイン',
                \App\Constants\Routes::SIGNIN
            );
            return;
        }

        $this->redirectSelf(self::ERROR_SYSTEM);
    }

    private function signinPost(): void
    {
        $form = new \App\Forms\SigninForm();

        if ($error = $this->checkCsrf($form->token())) {
            $this->redirectSelf($error, $form->old());
        }

        if ($error = $form->validate()) {
            $this->redirectSelf($error, $form->old());
        }

        $user = \App\Models\UserRepository::findByEmail($form->mail());

        $valid = $user
            ? $user->verifyPassword($form->pass())
            : password_verify($form->pass(), self::DUMMY_HASH);

        if (!$valid) {

            if (\App\Core\Security\LoginThrottle::hit()) {
                $this->redirectSelf(self::ERROR_LOCKED, $form->old());
            }

            $this->redirectSelf(self::ERROR_LOGIN, $form->old());
        }

        \App\Core\Security\LoginThrottle::clear();

        \App\Core\Http\Session::login($user);

        $this->redirect(\App\Constants\Routes::HOME);
    }

    public function signout(): void
    {
        $this->requireLogin();

        if (\App\Core\Http\Request::isPost()) {
            $this->signoutPost();
            return;
        }

        $this->redirectSelf(self::ERROR_SYSTEM);
    }

    private function signoutPost(): void
    {
        \App\Core\Http\Session::logout();

        $this->redirect(\App\Constants\Routes::SIGNIN);
    }

    public function mypage(): void
    {
        $this->requireLogin();

        if (\App\Core\Http\Request::isGet()) {
            $user = \App\Models\UserRepository::findById(\App\Core\Http\Session::userId());

            if (!$user) {
                \App\Core\Http\Session::logout();
                $this->redirect(\App\Constants\Routes::SIGNIN);
            }

            $this->render('auth/mypage', [
                'title'     => 'マイページ',
                'token'     => \App\Core\Security\Csrf::token(),
                'error'     => \App\Core\Http\Session::error(),
                'old'       => \App\Core\Http\Session::old(),
                'user'      => $user,
                'actionUrl' => \App\Constants\Routes::MYPAGE,
            ]);
            return;
        }

        if (\App\Core\Http\Request::isPost()) {
            $this->mypagePost();
        }

        $this->redirectSelf(self::ERROR_SYSTEM);
    }

    private function mypagePost(): void
    {
        $form = new \App\Forms\MypageForm();

        if ($error = $this->checkCsrf($form->token())) {
            $this->redirectSelf($error, $form->old());
        }

        if ($error = $form->validate()) {
            $this->redirectSelf($error, $form->old());
        }

        $userId = \App\Core\Http\Session::userId();
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
            'token'     => \App\Core\Security\Csrf::token(),
            'error'     => \App\Core\Http\Session::error(),
            'old'       => \App\Core\Http\Session::old(),
            'actionUrl' => $action,
        ]);
    }
}
