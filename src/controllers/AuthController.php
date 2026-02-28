<?php

declare(strict_types=1);

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/User.php';

class AuthController extends Controller
{
    private const LOCK = [
        'max'      => 5,
        'minutes'  => 15,
        'timeout'  => 900,
    ];

    private const ERRORS = [
        'invalid' => '不正なリクエストです',
        'input'   => '不正な入力です',
        'exists'  => 'このメールアドレスは既に登録されています',
        'create'  => 'ユーザーの登録に失敗しました',
        'login'   => 'メールアドレスまたはパスワードが違います',
        'locked'  => 'ログイン試行回数が上限に達しました。'.self::LOCK['minutes'].'分後にお試しください',
    ];

    public function signup(): void
    {
        if (Request::isGet()) {
            $this->render('auth/signup', $this->viewData(
                'サインアップ',
                '/signup',
                '/signin',
                'サインインはこちら'
            ));
            return;
        }

        $this->checkCsrf();
        $input = $this->input(true);

        if (User::findByEmail($input['mail'])) {
            $this->backWithError(self::ERRORS['exists']);
            return;
        }

        if (!User::create($input['name'], $input['mail'], $input['pass'])) {
            $this->backWithError(self::ERRORS['create']);
            return;
        }

        $this->login($input['mail']);
    }

    public function signin(): void
    {
        if (Request::isGet()) {
            $this->render('auth/signin', $this->viewData(
                'サインイン',
                '/signin',
                '/signup',
                'サインアップはこちら'
            ));
            return;
        }

        if ($this->isLocked()) {
            $this->backWithError(self::ERRORS['locked']);
            return;
        }

        $this->checkCsrf();
        $input = $this->input(false);

        if (!User::verifyPassword($input['mail'], $input['pass'])) {
            $this->addAttempt();
            return;
        }

        $this->resetAttempts();
        $this->login($input['mail']);
    }

    public function signout(): void
    {
        $this->requireLogin();

        Session::destroy();
        Session::regenerate();
        $this->redirect('/signin');
    }

    public function mypage(): void
    {
        $this->requireLogin();

        if (Request::isGet()) {
            $this->render('auth/mypage', [
                'title'     => 'マイページ',
                'token'     => Csrf::token(),
                'user'      => User::findById(Session::get(SessionKeys::USER_ID)),
                'actionUrl' => '/mypage',
            ]);
            return;
        }

        $this->checkCsrf();
        $input = [
            'name' => Request::post(FormFields::NAME, ''),
            'mail' => Request::post(FormFields::MAIL, ''),
            'pass' => Request::post(FormFields::PASS, ''),
            'pass_current' => Request::post(FormFields::PASS_CURRENT, ''),
        ];

        if ($input['pass'] && $input['pass'] !== Request::post(FormFields::PASS_CONFIRM, '')) {
            $this->backWithError(self::ERRORS['input']);
            return;
        }

        $userId = Session::get(SessionKeys::USER_ID);
        $user   = User::findById($userId);

        if (!User::verifyPassword($user['email'], $input['pass_current'])) {
            $this->backWithError(self::ERRORS['input']);
            return;
        }

        if (!User::update(
            $userId,
            $input['name'] ?: null,
            $input['mail'] ?: null,
            $input['pass'] ?: null
        )) {
            $this->backWithError('更新に失敗しました');
            return;
        }

        $this->redirectSelf();
    }

    private function viewData(string $title, string $action, string $toggleUrl, string $toggleText): array
    {
        return [
            'title'      => $title,
            'token'      => Csrf::token(),
            'error'      => Session::getFlash('error'),
            'actionUrl'  => $action,
            'toggleUrl'  => $toggleUrl,
            'toggleText' => $toggleText,
        ];
    }

    private function checkCsrf(): void
    {
        if (!Csrf::verify(Request::post(FormFields::TOKEN))) {
            $this->backWithError(self::ERRORS['invalid']);
        }
    }

    private function input(bool $signup): array
    {
        $data = [
            'name' => Request::post(FormFields::NAME, ''),
            'mail' => Request::post(FormFields::MAIL, ''),
            'pass' => Request::post(FormFields::PASS, ''),
        ];

        if ($signup) {
            $data['pass_confirm'] = Request::post(FormFields::PASS_CONFIRM, '');
        }

        if ($signup && $data['name'] === '') {
            $this->backWithError(self::ERRORS['input']);
        }

        if ($data['mail'] === '' ||
            !filter_var($data['mail'], FILTER_VALIDATE_EMAIL)) {
            $this->backWithError(self::ERRORS['input']);
        }

        if ($data['pass'] === '') {
            $this->backWithError(self::ERRORS['input']);
        }

        if ($signup && $data['pass'] !== $data['pass_confirm']) {
            $this->backWithError(self::ERRORS['input']);
        }

        return $data;
    }

    private function login(string $email): void
    {
        $user = User::findByEmail($email);

        Session::regenerate();
        Session::set(SessionKeys::USER_ID, $user['id']);
        Session::set(SessionKeys::USER_NAME, $user['name']);

        $this->redirect('/home');
    }

    private function backWithError(string $msg): void
    {
        Session::flash(SessionKeys::ERRORS, $msg);
        $this->redirectSelf();
    }

    private function isLocked(): bool
    {
        $attempts = (int) Session::get(SessionKeys::LOGIN_ATTEMPTS, 0);
        $last = (int) Session::get(SessionKeys::LOGIN_ATTEMPT_TIME, 0);

        if ($last && time() - $last > self::LOCK['timeout']) {
            $this->resetAttempts();
            return false;
        }

        return $attempts >= self::LOCK['max'];
    }

    private function addAttempt(): void
    {
        $attempts = (int) Session::get(SessionKeys::LOGIN_ATTEMPTS, 0) + 1;

        Session::set(SessionKeys::LOGIN_ATTEMPTS, $attempts);
        Session::set(SessionKeys::LOGIN_ATTEMPT_TIME, time());

        $msg = $attempts >= self::LOCK['max']
            ? self::ERRORS['locked']
            : self::ERRORS['login'];

        $this->backWithError($msg);
    }

    private function resetAttempts(): void
    {
        Session::remove(SessionKeys::LOGIN_ATTEMPTS);
        Session::remove(SessionKeys::LOGIN_ATTEMPT_TIME);
    }
}
