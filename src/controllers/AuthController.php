<?php

declare(strict_types=1);

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/UserRepository.php';

class AuthController extends Controller
{
    private const LOCK_MAX = 5;
    private const LOCK_MINUTES = 15;
    private const LOCK_TIMEOUT = self::LOCK_MINUTES * 60;

    private const ERROR_CURRENT_PASSWORD  = '現在のパスワードが正しくありません';
    private const ERROR_EXISTS            = 'このメールアドレスは既に登録されています';
    private const ERROR_LOGIN             = 'メールアドレスまたはパスワードが正しくありません';
    private const ERROR_LOCKED            = 'ログイン試行回数が上限に達しました。しばらくしてから再度お試しください';
    private const ERROR_SYSTEM            = '処理に失敗しました。時間をおいて再度お試しください';

    public function signup(): void
    {
        if (Request::isGet()) {
            $this->renderForm('auth/signup', 'サインアップ', Routes::SIGNUP);
            return;
        }

        $form = new SignupForm();

        $this->checkCsrf($form->token());

        if ($error = $form->validate()) {
            Session::flash(SessionKeys::OLD, $form->old());
            $this->backWithError($error);
            return;
        }

        if (UserRepository::findByEmail($form->mail())) {
            Session::flash(SessionKeys::OLD, $form->old());
            $this->backWithError(self::ERROR_EXISTS);
            return;
        }

        $user = UserRepository::create(
            $form->name(),
            $form->mail(),
            $form->pass()
        );

        if (!$user) {
            Session::flash(SessionKeys::OLD, $form->old());
            $this->backWithError(self::ERROR_SYSTEM);
            return;
        }

        Session::login($user);
        $this->redirect(Routes::HOME);
    }

    public function signin(): void
    {
        if (Request::isGet()) {
            $this->renderForm('auth/signin', 'サインイン', Routes::SIGNIN);
            return;
        }

        if ($this->isLocked()) {
            $this->backWithError(self::ERROR_LOCKED);
            return;
        }

        $form = new SigninForm();

        $this->checkCsrf($form->token());

        if ($error = $form->validate()) {
            Session::flash(SessionKeys::OLD, $form->old());
            $this->backWithError($error);
            return;
        }

        $user = UserRepository::findByEmail($form->mail());

        if (!$user || !$user->verifyPassword($form->pass())) {
            Session::flash(SessionKeys::OLD, $form->old());
            $msg = $this->addAttempt();
            $this->backWithError($msg);
            return;
        }

        $this->resetAttempts();

        Session::login($user);
        $this->redirect(Routes::HOME);
    }

    public function signout(): void
    {
        $this->requireLogin();

        Session::logout();

        $this->redirect(Routes::SIGNIN);
    }

    public function mypage(): void
    {
        $this->requireLogin();

        if (Request::isGet()) {
            $this->render('auth/mypage', [
                'title'     => 'マイページ',
                'token'     => Csrf::token(),
                'error'     => Session::getFlash(SessionKeys::ERRORS),
                'old'       => Session::getFlash(SessionKeys::OLD),
                'user'      => UserRepository::findById(Session::userId()),
                'actionUrl' => Routes::MYPAGE,
            ]);
            return;
        }

        $form = new MypageForm();

        $this->checkCsrf($form->token());

        if ($error = $form->validate()) {
            Session::flash(SessionKeys::OLD, $form->old());
            $this->backWithError($error);
            return;
        }

        $userId = Session::userId();
        $user   = UserRepository::findById($userId);

        if (!$user || !$user->verifyPassword($form->passCurrent())) {
            Session::flash(SessionKeys::OLD, $form->old());
            $this->backWithError(self::ERROR_CURRENT_PASSWORD);
            return;
        }

        if (!UserRepository::update(
            $userId,
            $form->name() ?: null,
            $form->mail() ?: null,
            $form->pass() ?: null
        )) {
            Session::flash(SessionKeys::OLD, $form->old());
            $this->backWithError(self::ERROR_SYSTEM);
            return;
        }

        $this->redirectSelf();
    }

    private function renderForm(string $view, string $title, string $action): void
    {
        $this->render($view, [
            'title'     => $title,
            'token'     => Csrf::token(),
            'error'     => Session::getFlash(SessionKeys::ERRORS),
            'old'       => Session::getFlash(SessionKeys::OLD),
            'actionUrl' => $action,
        ]);
    }

    private function backWithError(string $msg): void
    {
        Session::flash(SessionKeys::ERRORS, $msg);
        $this->redirectSelf();
    }

    private function isLocked(): bool
    {
        $attempts = (int) Session::get(SessionKeys::LOGIN_ATTEMPTS, 0);
        $last     = (int) Session::get(SessionKeys::LOGIN_ATTEMPT_TIME, 0);

        if ($last && time() - $last > self::LOCK_TIMEOUT) {
            $this->resetAttempts();
            return false;
        }

        return $attempts >= self::LOCK_MAX;
    }

    private function addAttempt(): string
    {
        $attempts = (int) Session::get(SessionKeys::LOGIN_ATTEMPTS, 0) + 1;

        Session::set(SessionKeys::LOGIN_ATTEMPTS, $attempts);
        Session::set(SessionKeys::LOGIN_ATTEMPT_TIME, time());

        $msg = $attempts >= self::LOCK_MAX
            ? self::ERROR_LOCKED
            : self::ERROR_LOGIN;

        return $msg;
    }

    private function resetAttempts(): void
    {
        Session::remove(SessionKeys::LOGIN_ATTEMPTS);
        Session::remove(SessionKeys::LOGIN_ATTEMPT_TIME);
    }
}
