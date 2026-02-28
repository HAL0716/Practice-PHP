<?php

declare(strict_types=1);

require_once __DIR__ . '/../core/Controller.php';

class HomeController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();

        $title = 'ホーム';

        $this->render(
            'home',
            [
                'title'      => $title,
                'username'   => Session::get(SessionKeys::USER_NAME),
            ]
        );
    }
}
