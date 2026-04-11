<?php

declare(strict_types=1);

namespace App\Application\Controllers;

use App\Application\Constants\RoutePaths;
use App\Application\Forms\Post\CreateForm;
use App\Application\Forms\Post\DeleteForm;
use App\Application\Http\Controller;
use App\Application\Http\RequestInterface;
use App\Application\Http\ResponseInterface;
use App\Application\Http\SessionInterface;
use App\Application\Security\CsrfInterface;
use App\Domain\Post\PostRepositoryInterface;

final class PostController extends Controller
{
    public function __construct(
        RequestInterface $request,
        SessionInterface $session,
        CsrfInterface $csrf,
        private PostRepositoryInterface $posts
    ) {
        parent::__construct($request, $session, $csrf);
    }

    public function home(): ResponseInterface
    {
        if ($res = $this->requireLogin()) {
            return $res;
        }

        return $this->dispatch(
            get: fn () => $this->showPosts(),
            post: fn () => $this->createPost()
        );
    }

    public function delete(): ResponseInterface
    {
        if ($res = $this->requireLogin()) {
            return $res;
        }

        return $this->dispatch(
            post: fn () => $this->deletePost()
        );
    }

    private function showPosts(): ResponseInterface
    {
        return $this->render('post/home', [
            'user_id' => $this->userId(),
            'posts'   => $this->posts->findAll(),
        ]);
    }

    private function createPost(): ResponseInterface
    {
        $form = new CreateForm($this->request);

        if ($res = $this->ensureValidForm($form)) {
            return $res;
        }

        $userId = $this->userId();

        if (!$this->posts->create($userId, $form->comment())) {
            return $this->redirectSelf(self::ERROR_SYSTEM, $form->old());
        }

        return $this->redirectSelf();
    }

    private function deletePost(): ResponseInterface
    {
        $form = new DeleteForm($this->request);

        if ($res = $this->ensureValidForm($form)) {
            return $res;
        }

        $userId = $this->userId();

        if (!$this->posts->delete($form->id(), $userId)) {
            return $this->redirect(RoutePaths::POST_HOME, self::ERROR_SYSTEM);
        }

        return $this->redirect(RoutePaths::POST_HOME);
    }
}
