<?php

declare(strict_types=1);

require_once __DIR__ . '/../core/Controller.php';

class HomeController extends Controller
{
    public function index(): void
    {
        $title = 'ホーム';

        $this->render(
            'home',
            [
                'title'      => $title,
                'isLoggedIn' => $this->isLoggedIn(),
                'username'   => Session::get('user_name'),
            ]
        );
    }
}
