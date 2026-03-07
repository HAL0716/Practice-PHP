<?php

declare(strict_types=1);

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/LoginThrottle.php';
require_once __DIR__ . '/../models/UserRepository.php';

class AuthController extends Controller
{
    private const ERROR_PASSWORD = '現在のパスワードが正しくありません';
    private const ERROR_EXISTS   = 'このメールアドレスは既に登録されています';
    private const ERROR_LOGIN    = 'メールアドレスまたはパスワードが正しくありません';
    private const ERROR_LOCKED   = 'ログイン試行回数が上限に達しました。しばらくしてから再度お試しください';
    private const ERROR_SYSTEM   = '処理に失敗しました。時間をおいて再度お試しください';

    public function signup(): void
    {
        if (Request::isGet()) {
            $this->renderForm('auth/signup', 'サインアップ', Routes::SIGNUP);
            return;
        }

        if (!Request::isPost()) {
            $this->redirectSelf(self::ERROR_SYSTEM);
        }

        $form = new SignupForm();

        $this->checkCsrf($form->token());

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
        if (Request::isGet()) {
            $this->renderForm('auth/signin', 'サインイン', Routes::SIGNIN);
            return;
        }

        if (!Request::isPost()) {
            $this->redirectSelf(self::ERROR_SYSTEM);
        }

        if (LoginThrottle::isLocked()) {
            $this->redirectSelf(self::ERROR_LOCKED);
        }

        $form = new SigninForm();

        $this->checkCsrf($form->token());

        if ($error = $form->validate()) {
            $this->redirectSelf($error, $form->old());
        }

        $user = UserRepository::findByEmail($form->mail());

        if (!$user || !$user->verifyPassword($form->pass())) {

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
        if (!Request::isPost()) {
            $this->redirectSelf(self::ERROR_SYSTEM);
        }

        $this->requireLogin();

        Session::logout();

        $this->redirect(Routes::SIGNIN);
    }

    public function mypage(): void
    {
        $this->requireLogin();

        if (!Request::isPost()) {
            $this->redirectSelf(self::ERROR_SYSTEM);
        }

        if (Request::isGet()) {
            $this->render('auth/mypage', [
                'title'     => 'マイページ',
                'token'     => Csrf::token(),
                'error'     => Session::error(),
                'old'       => Session::old(),
                'user'      => UserRepository::findById(Session::userId()),
                'actionUrl' => Routes::MYPAGE,
            ]);
            return;
        }

        $form = new MypageForm();

        $this->checkCsrf($form->token());

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
            $form->name() ?: null,
            $form->mail() ?: null,
            $form->pass() ?: null
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
