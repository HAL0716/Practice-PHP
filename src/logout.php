<?php

declare(strict_types=1);

$_SESSION = array();
session_destroy();
header('Location: /auth');
exit;
