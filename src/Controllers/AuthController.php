<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Constants\Routes;
use App\Core\Http\Controller;
use App\Core\Http\Request;
use App\Core\Http\Session;
use App\Core\Security\LoginThrottle;
use App\Domain\User\User;
use App\Domain\User\UserRepository;
use App\Forms\User\DeleteForm;
use App\Forms\User\UpdateForm;
use App\Forms\User\SigninForm;
use App\Forms\User\SignupForm;

final class AuthController extends Controller
{
    private const DUMMY_HASH = '$2y$10$wH3Gm1H4qJ5FQGqV3y4kUe1xW8Vh3kQn6YbK7QeY8bJ2sD0m9F8aK';

    private const ERROR_PASSWORD = '現在のパスワードが正しくありません';
    private const ERROR_EXISTS   = 'このメールアドレスは既に登録されています';
    private const ERROR_LOGIN    = 'メールアドレスまたはパスワードが正しくありません';

    public function signup(): void
    {
        $this->dispatch(
            post: fn () => $this->createUser(),
            get:  fn () => $this->render('auth/signup')
        );
    }

    public function signin(): void
    {
        $this->dispatch(
            post: fn () => $this->authUser(),
            get:  fn () => $this->render('auth/signin')
        );
    }

    public function signout(): void
    {
        $this->requireLogin();

        $this->dispatch(
            get: fn () => $this->logoutUser()
        );
    }

    public function delete(): void
    {
        $this->requireLogin();

        $this->dispatch(
            post: fn () => $this->deleteUser()
        );
    }

    public function mypage(): void
    {
        $this->requireLogin();

        $this->dispatch(
            post: fn () => $this->updateUser(),
            get:  fn () => $this->showMypage()
        );
    }

    private function createUser(): void
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

    private function authUser(): void
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

            if ($error = LoginThrottle::hit()) {
                $this->redirectSelf($error, $form->old());
            }

            $this->redirectSelf(self::ERROR_LOGIN, $form->old());
        }

        LoginThrottle::clear();

        Session::login($user);

        $this->redirect(Routes::HOME);
    }

    private function logoutUser(): void
    {
        Session::logout();
        $this->redirect(Routes::SIGNIN);
    }

    private function deleteUser(): void
    {
        $form = new DeleteForm();

        if (!$this->ensureValidForm($form, Routes::MYPAGE)) {
            return;
        }

        if (!$this->ensureValidPassword($form->passCurrent(), Routes::MYPAGE)) {
            return;
        }

        if (!UserRepository::delete($this->userId())) {
            $this->redirect(Routes::MYPAGE, self::ERROR_SYSTEM);
        }

        $this->logoutUser();
    }

    private function showMypage(): void
    {
        $user = $this->currentUser();

        if ($user === null) {
            $this->logoutUser();
        }

        $this->render('auth/mypage', ['user' => $user]);
    }

    private function updateUser(): void
    {
        $form = new UpdateForm();

        if (!$this->ensureValidForm($form)) {
            return;
        }

        if (!$this->ensureValidPassword($form->passCurrent())) {
            return;
        }

        if (!UserRepository::update(
            $this->userId(),
            $this->nullable($form->name()),
            $this->nullable($form->mail()),
            $this->nullable($form->pass())
        )) {
            $this->redirectSelf(self::ERROR_SYSTEM, $form->old());
        }

        $this->redirectSelf();
    }

    private function currentUser(): ?User
    {
        return UserRepository::findById($this->userId());
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

    private function nullable(string $value): ?string
    {
        return $value === '' ? null : $value;
    }
}
