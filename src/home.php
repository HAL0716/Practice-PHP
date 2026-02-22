<?php

declare(strict_types=1);

$title = 'ホーム';

$username = Session::get('user_name');

ob_start();
require __DIR__ . "/views/home.php";
$content = ob_get_clean();
require __DIR__ . '/views/layouts/default.php';
