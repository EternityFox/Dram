<?php declare(strict_types=1);

use App\App;

ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../storage/error.log');

date_default_timezone_set('Asia/Yerevan');

require_once __DIR__ . '/../sys/Core/inc/init.php';
require_once __DIR__ . '/functions.php';

return new App(
    (include(__DIR__ . '/config.php'))
    + (include(__DIR__ . '/../storage/config.php'))
);
