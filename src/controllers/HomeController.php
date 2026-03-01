<?php

declare(strict_types=1);

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Post.php';

class HomeController extends Controller
{
    private const ERROR_CSRF             = '不正なリクエストです。再度お試しください';
    private const ERROR_COMMENT_REQUIRED = 'コメントは必須です。';
    private const ERROR_SYSTEM           = '処理に失敗しました。時間をおいて再度お試しください';

    public function index(): void
    {
        $this->requireLogin();

        $this->render('home', [
                'title'  => 'ホーム',
                'token'  => Csrf::token(),
                'errors' => Session::getFlash(SessionKeys::ERRORS),
                'posts'  => Post::findAll(),
            ]
        );
    }

    public function createPost(): void
    {
        if (!Request::isPost()) {
            http_response_code(405);
            echo '405 Method Not Allowed';
            exit;
        }

        $this->checkCsrf();

        $this->requireLogin();

        $form = $this->postForm([
            FormFields::COMMENT,
        ]);

        if ($this->hasEmpty($form)) {
            $this->backWithError(self::ERROR_COMMENT_REQUIRED);
            return;
        }

        $userId  = Session::userId();

        if (!Post::create($userId, $form[FormFields::COMMENT])) {
            $this->backWithError(self::ERROR_SYSTEM);
            return;
        }

        $this->redirect(Routes::HOME);
    }

    private function checkCsrf(): void
    {
        if (!Csrf::verify(Request::post(FormFields::TOKEN))) {
            $this->backWithError(self::ERROR_CSRF);
        }
    }

    private function postForm(array $fields): array
    {
        $data = [];
        foreach ($fields as $field) {
            $data[$field] = Request::post($field, '');
        }

        $this->flashOld([
            FormFields::COMMENT => $data[FormFields::COMMENT] ?? '',
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

    private function backWithError(string $msg): void
    {
        Session::flash(SessionKeys::ERRORS, $msg);
        $this->redirectSelf();
    }
}
