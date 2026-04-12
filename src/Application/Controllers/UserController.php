<?php

declare(strict_types=1);

namespace App\Application\Controllers;

use App\Application\Constants\RoutePaths;
use App\Application\Http\Controller;
use App\Application\Forms\User\DeleteForm;
use App\Application\Forms\User\UpdateForm;
use App\Application\Forms\User\SigninForm;
use App\Application\Forms\User\SignupForm;
use App\Application\Http\RequestInterface;
use App\Application\Http\ResponseInterface;
use App\Application\Http\SessionInterface;
use App\Application\Security\CsrfInterface;
use App\Application\Security\LoginThrottleInterface;
use App\Domain\User\User;
use App\Domain\User\UserRepositoryInterface;

final class UserController extends Controller
{
    private const DUMMY_HASH = '$2y$10$wH3Gm1H4qJ5FQGqV3y4kUe1xW8Vh3kQn6YbK7QeY8bJ2sD0m9F8aK';

    public const ERROR_PASSWORD = '現在のパスワードが正しくありません';
    public const ERROR_EXISTS   = 'このメールアドレスは既に登録されています';
    public const ERROR_LOGIN    = 'メールアドレスまたはパスワードが正しくありません';

    public function __construct(
        RequestInterface $request,
        SessionInterface $session,
        CsrfInterface $csrf,
        private UserRepositoryInterface $users,
        private LoginThrottleInterface $throttle
    ) {
        parent::__construct($request, $session, $csrf);
    }

    public function signup(): ResponseInterface
    {
        return $this->dispatch(
            post: fn () => $this->createUser(),
            get:  fn () => $this->render('user/signup')
        );
    }

    public function signin(): ResponseInterface
    {
        return $this->dispatch(
            post: fn () => $this->authUser(),
            get:  fn () => $this->render('user/signin')
        );
    }

    public function signout(): ResponseInterface
    {
        if ($res = $this->requireLogin()) {
            return $res;
        }

        return $this->dispatch(
            get: fn () => $this->logoutUser()
        );
    }

    public function delete(): ResponseInterface
    {
        if ($res = $this->requireLogin()) {
            return $res;
        }

        return $this->dispatch(
            post: fn () => $this->deleteUser()
        );
    }

    public function mypage(): ResponseInterface
    {
        if ($res = $this->requireLogin()) {
            return $res;
        }

        return $this->dispatch(
            post: fn () => $this->updateUser(),
            get:  fn () => $this->showMypage()
        );
    }

    private function createUser(): ResponseInterface
    {
        $form = new SignupForm($this->request);

        if ($res = $this->ensureValidForm($form)) {
            return $res;
        }

        if ($this->users->findByEmail($form->mail())) {
            return $this->redirectSelf(self::ERROR_EXISTS, $form->old());
        }

        $user = $this->users->create(
            $form->name(),
            $form->mail(),
            $form->pass()
        );

        if (!$user) {
            return $this->redirectSelf(self::ERROR_SYSTEM, $form->old());
        }

        $this->session->login($user);

        return $this->redirect(RoutePaths::POST_HOME);
    }

    private function authUser(): ResponseInterface
    {
        $form = new SigninForm($this->request);

        if ($res = $this->ensureValidForm($form)) {
            return $res;
        }

        $user = $this->users->findByEmail($form->mail());

        $valid = $user
            ? $user->verifyPassword($form->pass())
            : password_verify($form->pass(), self::DUMMY_HASH);

        if (!$valid || $user === null) {

            if ($error = $this->throttle->hit()) {
                return $this->redirectSelf($error, $form->old());
            }

            return $this->redirectSelf(self::ERROR_LOGIN, $form->old());
        }

        $this->throttle->clear();

        $this->session->login($user);

        return $this->redirect(RoutePaths::POST_HOME);
    }

    private function logoutUser(): ResponseInterface
    {
        $this->session->logout();
        return $this->redirect(RoutePaths::USER_SIGNIN);
    }

    private function deleteUser(): ResponseInterface
    {
        $form = new DeleteForm($this->request);

        if ($res = $this->ensureValidForm($form, RoutePaths::USER_MYPAGE)) {
            return $res;
        }

        if ($res = $this->ensureValidPassword($form->passCurrent(), RoutePaths::USER_MYPAGE)) {
            return $res;
        }

        if (!$this->users->delete($this->userId())) {
            return $this->redirect(RoutePaths::USER_MYPAGE, self::ERROR_SYSTEM);
        }

        return $this->logoutUser();
    }

    private function showMypage(): ResponseInterface
    {
        $user = $this->currentUser();

        if ($user === null) {
            return $this->logoutUser();
        }

        return $this->render('user/mypage', ['user' => $user]);
    }

    private function updateUser(): ResponseInterface
    {
        $form = new UpdateForm($this->request);

        if ($res = $this->ensureValidForm($form)) {
            return $res;
        }

        if ($res = $this->ensureValidPassword($form->passCurrent())) {
            return $res;
        }

        if ($form->mail() && $this->users->findByEmail($form->mail())) {
            return $this->redirectSelf(self::ERROR_EXISTS, $form->old());
        }

        if (!$this->users->update(
            $this->userId(),
            $this->nullable($form->name()),
            $this->nullable($form->mail()),
            $this->nullable($form->pass())
        )) {
            return $this->redirectSelf(self::ERROR_SYSTEM, $form->old());
        }

        return $this->redirectSelf();
    }

    private function currentUser(): ?User
    {
        return $this->users->findById($this->userId());
    }

    private function ensureValidPassword(string $password, ?string $redirect = null): ?ResponseInterface
    {
        $redirect ??= $this->request->path();

        $user = $this->currentUser();

        if (!$user || !$user->verifyPassword($password)) {
            return $this->redirect($redirect, self::ERROR_PASSWORD);
        }

        return null;
    }

    private function nullable(string $value): ?string
    {
        return $value === '' ? null : $value;
    }
}
