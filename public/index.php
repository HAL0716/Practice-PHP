<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/constants/FormFields.php';
require_once __DIR__ . '/../src/constants/Routes.php';
require_once __DIR__ . '/../src/constants/SessionKeys.php';

require_once __DIR__ . '/../src/core/Html.php';
require_once __DIR__ . '/../src/core/Request.php';
require_once __DIR__ . '/../src/core/Session.php';
require_once __DIR__ . '/../src/core/Csrf.php';

require_once __DIR__ . '/../src/controllers/HomeController.php';
require_once __DIR__ . '/../src/controllers/AuthController.php';

require_once __DIR__ . '/../src/form/SigninForm.php';

$url = Request::path();

switch ($url) {

    case Routes::HOME:
        $controller = new HomeController();
        $controller->index();
        exit;

    case Routes::SIGNUP:
        $controller = new AuthController();
        $controller->signup();
        exit;

    case Routes::SIGNIN:
        $controller = new AuthController();
        $controller->signin();
        exit;

    case Routes::SIGNOUT:
        $controller = new AuthController();
        $controller->signout();
        exit;

    case Routes::MYPAGE:
        $controller = new AuthController();
        $controller->mypage();
        exit;

    case Routes::POST_CREATE:
        $controller = new HomeController();
        $controller->createPost();
        exit;

    default:
        http_response_code(404);
        echo '404 Not Found';
        exit;
}
