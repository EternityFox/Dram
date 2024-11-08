<?php declare(strict_types=1);

/** @var \App\App $app */
$app = include_once __DIR__ . '/app/inc/init.php';

try {
    $app->run();
} catch(Throwable $e) {
    header('Content-Type: text/plain; charset=utf-8');
    var_dump($e);
}
