<?php declare(strict_types=1);

namespace Core;

/**
 * Время инициализации приложения
 */
defined(__NAMESPACE__ . '\\STARTED_AT')
    OR define(__NAMESPACE__ . '\\STARTED_AT', microtime(true));
/**
 * Метка текущего времени (timestamp)
 */
define(__NAMESPACE__ . '\\TIME', (int) STARTED_AT);
/**
 * Директория приложения
 */
defined(__NAMESPACE__ . '\\APP_DIR')
    OR define(__NAMESPACE__ . '\\APP_DIR', realpath(__DIR__ . '/../../..'));
/**
 * Директория автозагрузчика
 */
defined(__NAMESPACE__ . '\\AUTOLOAD_DIR')
    OR define(__NAMESPACE__ . '\\AUTOLOAD_DIR', realpath(__DIR__ . '/../..'));

/**
 * Автозагрузчик
 */
if (AUTOLOAD_DIR)
    spl_autoload_register(function($class) {
        $file = AUTOLOAD_DIR . DIRECTORY_SEPARATOR . handleDS($class) . '.php';
        if (file_exists($file))
            include $file;
    });

/**
 * Обработка разделителей ФС
 *
 * @param string $path
 *
 * @return string
 */
function handleDS(string $path): string
{
    static
        $from = ('/' === DIRECTORY_SEPARATOR) ? '\\' : '/',
        $to = DIRECTORY_SEPARATOR;

    return str_replace($from, $to, $path);
}
