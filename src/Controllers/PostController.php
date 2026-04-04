<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Constants\Routes;
use App\Contracts\Domain\Post\PostRepositoryInterface;
use App\Contracts\Http\RequestInterface;
use App\Contracts\Http\ResponseInterface;
use App\Contracts\Http\SessionInterface;
use App\Contracts\Security\CsrfInterface;
use App\Core\Http\Controller;
use App\Forms\Post\CreateForm;
use App\Forms\Post\DeleteForm;

final class PostController extends Controller
{
    public function __construct(
        RequestInterface $request,
        SessionInterface $session,
        ResponseInterface $response,
        CsrfInterface $csrf,
        private PostRepositoryInterface $posts
    ) {
        parent::__construct($request, $session, $response, $csrf);
    }

    public function home(): void
    {
        $this->requireLogin();

        $this->dispatch(
            get: fn () => $this->showPosts(),
            post: fn () => $this->createPost()
        );
    }

    public function delete(): void
    {
        $this->requireLogin();

        $this->dispatch(
            post: fn () => $this->deletePost()
        );
    }

    private function showPosts(): void
    {
        $this->render('post/home', [
            'user_id' => $this->userId(),
            'posts'   => $this->posts->findAll(),
        ]);
    }

    private function createPost(): void
    {
        $form = new CreateForm($this->request);

        if (!$this->ensureValidForm($form)) {
            return;
        }

        if (!$this->posts->create($this->userId(), $form->comment())) {
            $this->redirectSelf(self::ERROR_SYSTEM, $form->old());
        }

        $this->redirectSelf();
    }

    private function deletePost(): void
    {
        $form = new DeleteForm($this->request);

        if (!$this->ensureValidForm($form)) {
            return;
        }

        if (!$this->posts->delete($form->id(), $this->userId())) {
            $this->redirect(Routes::POST_HOME, self::ERROR_SYSTEM);
        }

        $this->redirect(Routes::POST_HOME);
    }
}
