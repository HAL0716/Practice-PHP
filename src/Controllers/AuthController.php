<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Constants\Routes;
use App\Core\Http\Controller;
use App\Core\Http\Request;
use App\Core\Http\Session;
use App\Core\Security\Csrf;
use App\Core\Security\LoginThrottle;
use App\Forms\MypageForm;
use App\Forms\SigninForm;
use App\Forms\SignupForm;
use App\Models\UserRepository;

final class AuthController extends Controller
{
    private const DUMMY_HASH = '$2y$10$wH3Gm1H4qJ5FQGqV3y4kUe1xW8Vh3kQn6YbK7QeY8bJ2sD0m9F8aK';

    private const ERROR_PASSWORD = '現在のパスワードが正しくありません';
    private const ERROR_EXISTS   = 'このメールアドレスは既に登録されています';
    private const ERROR_LOGIN    = 'メールアドレスまたはパスワードが正しくありません';
    private const ERROR_LOCKED   = 'ログイン試行回数が上限に達しました。しばらくしてから再度お試しください';

    public function signup(): void
    {
        if (Request::isPost()) {
            $this->signupPost();
            return;
        }

        if (Request::isGet()) {
            $this->renderForm(
                'auth/signup',
                'サインアップ',
                Routes::SIGNUP
            );
            return;
        }

        $this->redirectSelf(self::ERROR_SYSTEM);
    }

    private function signupPost(): void
    {
        $form = new SignupForm();

        if ($error = $this->checkCsrf($form->token())) {
            $this->redirectSelf($error, $form->old());
        }

        if ($error = $form->validate()) {
            $this->redirectSelf($error, $form->old());
        }

        if (UserRepository::findByEmail($form->mail())) {
            $this->redirectSelf(self::ERROR_EXISTS, $form->old());
        }

        $user = UserRepository::create(
            $form->name(),
            $form->mail(),
            $form->pass()
        );

        if (!$user) {
            $this->redirectSelf(self::ERROR_SYSTEM, $form->old());
        }

        Session::login($user);

        $this->redirect(Routes::HOME);
    }

    public function signin(): void
    {
        if (Request::isPost()) {
            $this->signinPost();
            return;
        }

        if (Request::isGet()) {
            $this->renderForm(
                'auth/signin',
                'サインイン',
                Routes::SIGNIN
            );
            return;
        }

        $this->redirectSelf(self::ERROR_SYSTEM);
    }

    private function signinPost(): void
    {
        $form = new SigninForm();

        if ($error = $this->checkCsrf($form->token())) {
            $this->redirectSelf($error, $form->old());
        }

        if ($error = $form->validate()) {
            $this->redirectSelf($error, $form->old());
        }

        $user = UserRepository::findByEmail($form->mail());

        $valid = $user
            ? $user->verifyPassword($form->pass())
            : password_verify($form->pass(), self::DUMMY_HASH);

        if (!$valid) {

            if (LoginThrottle::hit()) {
                $this->redirectSelf(self::ERROR_LOCKED, $form->old());
            }

            $this->redirectSelf(self::ERROR_LOGIN, $form->old());
        }

        LoginThrottle::clear();

        Session::login($user);

        $this->redirect(Routes::HOME);
    }

    public function signout(): void
    {
        $this->requireLogin();

        if (Request::isPost()) {
            $this->signoutPost();
            return;
        }

        $this->redirectSelf(self::ERROR_SYSTEM);
    }

    private function signoutPost(): void
    {
        Session::logout();

        $this->redirect(Routes::SIGNIN);
    }

    public function mypage(): void
    {
        $this->requireLogin();

        if (Request::isGet()) {
            $user = UserRepository::findById(Session::userId());

            if (!$user) {
                Session::logout();
                $this->redirect(Routes::SIGNIN);
            }

            $this->render('auth/mypage', [
                'title'     => 'マイページ',
                'token'     => Csrf::token(),
                'error'     => Session::error(),
                'old'       => Session::old(),
                'user'      => $user,
                'actionUrl' => Routes::MYPAGE,
            ]);
            return;
        }

        if (Request::isPost()) {
            $this->mypagePost();
        }

        $this->redirectSelf(self::ERROR_SYSTEM);
    }

    private function mypagePost(): void
    {
        $form = new MypageForm();

        if ($error = $this->checkCsrf($form->token())) {
            $this->redirectSelf($error, $form->old());
        }

        if ($error = $form->validate()) {
            $this->redirectSelf($error, $form->old());
        }

        $userId = Session::userId();
        $user   = UserRepository::findById($userId);

        if (!$user || !$user->verifyPassword($form->passCurrent())) {
            $this->redirectSelf(self::ERROR_PASSWORD, $form->old());
        }

        if (!UserRepository::update(
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
            'token'     => Csrf::token(),
            'error'     => Session::error(),
            'old'       => Session::old(),
            'actionUrl' => $action,
        ]);
    }
}
