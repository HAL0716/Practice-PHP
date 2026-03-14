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
use App\Forms\DeleteForm;
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
        $this->dispatch(
            post: fn () => $this->signupPost(),
            get:  fn () => $this->renderForm('auth/signup')
        );
    }

    private function signupPost(): void
    {
        $form = new SignupForm();

        if (!$this->ensureValidForm($form)) {
            return;
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
        $this->dispatch(
            post: fn () => $this->signinPost(),
            get:  fn () => $this->renderForm('auth/signin')
        );
    }

    private function signinPost(): void
    {
        $form = new SigninForm();

        if (!$this->ensureValidForm($form)) {
            return;
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

        $this->dispatch(
            get: function () {
                Session::logout();
                $this->redirect(Routes::SIGNIN);
            }
        );
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
        $form = new DeleteForm();

        if (!$this->ensureValidForm($form)) {
            return;
        }

        if (!$this->ensureValidPassword($form->passCurrent(), Routes::MYPAGE)) {
            return;
        }

        if (!UserRepository::delete(Session::userId())) {
            $this->redirect(Routes::MYPAGE, self::ERROR_SYSTEM);
        }

        Session::logout();
        $this->redirect(Routes::SIGNIN);
    }

    public function mypage(): void
    {
        $this->requireLogin();

        $this->dispatch(
            post: fn () => $this->mypagePost(),
            get:  fn () => $this->mypageGet()
        );
    }

    private function mypageGet(): void
    {
        if (!$user = $this->currentUser()) {
            Session::logout();
            $this->redirect(Routes::SIGNIN);
        }

        $this->render('auth/mypage', [
            'token'     => Csrf::token(),
            'error'     => Session::error(),
            'old'       => Session::old(),
            'user'      => $user,
        ]);
    }

    private function mypagePost(): void
    {
        $form = new MypageForm();

        if (!$this->ensureValidForm($form)) {
            return;
        }

        if (!$this->ensureValidPassword($form->passCurrent())) {
            return;
        }

        if (!UserRepository::update(
            Session::userId(),
            $form->name() === '' ? null : $form->name(),
            $form->mail() === '' ? null : $form->mail(),
            $form->pass() === '' ? null : $form->pass()
        )) {
            $this->redirectSelf(self::ERROR_SYSTEM, $form->old());
        }

        $this->redirectSelf();
    }

    private function renderForm(string $view): void
    {
        $this->render($view, [
            'token'     => Csrf::token(),
            'error'     => Session::error(),
            'old'       => Session::old(),
        ]);
    }

    private function currentUser()
    {
        return UserRepository::findById(Session::userId());
    }

    private function ensureValidPassword(string $password, ?string $redirect = null): bool
    {
        $redirect ??= Request::path();

        $user = $this->currentUser();

        if (!$user || !$user->verifyPassword($password)) {
            $this->redirect($redirect, self::ERROR_PASSWORD);
            return false;
        }

        return true;
    }
}
