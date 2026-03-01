<?php

declare(strict_types=1);

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/User.php';

class AuthController extends Controller
{
    private const LOCK_MAX = 5;
    private const LOCK_MINUTES = 15;
    private const LOCK_TIMEOUT = self::LOCK_MINUTES * 60;

    private const ERROR_CSRF              = '不正なリクエストです。再度お試しください';
    private const ERROR_INVALID_INPUT     = '入力内容を確認してください';
    private const ERROR_PASSWORD_MISMATCH = 'パスワード確認が一致しません';
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

        $this->checkCsrf();

        $form = $this->postForm([
            FormFields::NAME,
            FormFields::MAIL,
            FormFields::PASS,
            FormFields::PASS_CONFIRM,
        ]);

        if ($this->hasEmpty($form)) {
            $this->backWithError(self::ERROR_INVALID_INPUT);
            return;
        }

        if (!$this->passwordConfirmed($form)) {
            $this->backWithError(self::ERROR_PASSWORD_MISMATCH);
            return;
        }

        if (User::findByEmail($form[FormFields::MAIL])) {
            $this->backWithError(self::ERROR_EXISTS);
            return;
        }

        if (!User::create(
            $form[FormFields::NAME],
            $form[FormFields::MAIL],
            $form[FormFields::PASS]
        )) {
            $this->backWithError(self::ERROR_SYSTEM);
            return;
        }

        $this->login($form[FormFields::MAIL]);
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

        $this->checkCsrf();

        $form = $this->postForm([
            FormFields::MAIL,
            FormFields::PASS,
        ]);

        if ($this->hasEmpty($form)) {
            $this->backWithError(self::ERROR_INVALID_INPUT);
            return;
        }

        if (!User::verifyPassword($form[FormFields::MAIL], $form[FormFields::PASS])) {
            $this->addAttempt();
            return;
        }

        $this->resetAttempts();
        $this->login($form[FormFields::MAIL]);
    }

    public function signout(): void
    {
        $this->requireLogin();

        Session::destroy();
        Session::regenerate();

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
                'user'      => User::findById(Session::get(SessionKeys::USER_ID)),
                'actionUrl' => Routes::MYPAGE,
            ]);
            return;
        }

        $this->checkCsrf();

        $form = $this->postForm([
            FormFields::NAME,
            FormFields::MAIL,
            FormFields::PASS,
            FormFields::PASS_CONFIRM,
            FormFields::PASS_CURRENT,
        ]);

        if ($form[FormFields::NAME] === '' || $form[FormFields::MAIL] === '') {
            $this->backWithError(self::ERROR_INVALID_INPUT);
            return;
        }

        if ($form[FormFields::PASS] && !$this->passwordConfirmed($form)) {
            $this->backWithError(self::ERROR_PASSWORD_MISMATCH);
            return;
        }

        $userId = Session::get(SessionKeys::USER_ID);
        $user   = User::findById($userId);

        if (!User::verifyPassword($user[User::FIELD_EMAIL], $form[FormFields::PASS_CURRENT])) {
            $this->backWithError(self::ERROR_CURRENT_PASSWORD);
            return;
        }

        if (!User::update(
            $userId,
            $form[FormFields::NAME] ?: null,
            $form[FormFields::MAIL] ?: null,
            $form[FormFields::PASS] ?: null
        )) {
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

    private function postForm(array $fields): array
    {
        $data = [];
        foreach ($fields as $field) {
            $data[$field] = Request::post($field, '');
        }

        $this->flashOld([
            FormFields::NAME => $data[FormFields::NAME] ?? '',
            FormFields::MAIL => $data[FormFields::MAIL] ?? '',
        ]);

        return $data;
    }

    private function hasEmpty(array $data): bool
    {
        foreach ($data as $value) {
            if ($value === '') {
                return true;
            }
        }
        return false;
    }

    private function passwordConfirmed(array $form): bool
    {
        return $form[FormFields::PASS] === $form[FormFields::PASS_CONFIRM];
    }

    private function checkCsrf(): void
    {
        if (!Csrf::verify(Request::post(FormFields::TOKEN))) {
            $this->backWithError(self::ERROR_CSRF);
        }
    }

    private function login(string $email): void
    {
        $user = User::findByEmail($email);

        Session::regenerate();
        Session::set(SessionKeys::USER_ID, $user[User::FIELD_ID]);
        Session::set(SessionKeys::USER_NAME, $user[User::FIELD_USERNAME]);

        $this->redirect(Routes::HOME);
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

    private function addAttempt(): void
    {
        $attempts = (int) Session::get(SessionKeys::LOGIN_ATTEMPTS, 0) + 1;

        Session::set(SessionKeys::LOGIN_ATTEMPTS, $attempts);
        Session::set(SessionKeys::LOGIN_ATTEMPT_TIME, time());

        $msg = $attempts >= self::LOCK_MAX
            ? self::ERROR_LOCKED
            : self::ERROR_LOGIN;

        $this->backWithError($msg);
    }

    private function resetAttempts(): void
    {
        Session::remove(SessionKeys::LOGIN_ATTEMPTS);
        Session::remove(SessionKeys::LOGIN_ATTEMPT_TIME);
    }
}
