<?php

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

Session::destroy();
header('Location: /auth');
exit;
