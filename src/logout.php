<?php

declare(strict_types=1);

Session::destroy();
header('Location: /auth');
exit;
