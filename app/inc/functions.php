<?php declare(strict_types=1);

/**
 * @param int $size
 * @param int $precision
 * @param bool $trimmed
 *
 * @return string
 */
function bytes(int $size, int $precision = 2, bool $trimmed = true): string
{
    static $units = ['bytes', 'KB', 'MB', 'GB', 'TB'];

    if (1024 > $size)
        return "{$size} {$units[0]}";

    $pow = $size > 0 ? floor(log($size, 1024)) : 0;
    $size = number_format($size / pow(1024, $pow), $precision, '.', '');

    return ($trimmed ? rtrim($size, '0.') : $size) . " {$units[$pow]}";
}

function random_elem(...$elem)
{
    $c = count($elem) - 1;
    $rand = $elem[rand(0, $c)];
    if (trim($rand) == '' || !$rand) {
        foreach ($elem as $item) {
            strlen($item) > 2 ? $rand = $item : null;
        }
    }
    return $rand;
}

/**
 * @param array|\ArrayAccess $arr
 * @param array $trace
 * @param mixed $default
 *
 * @return mixed
 */
function arrayGet($arr, array $trace, $default = null)
{
    foreach ($trace as $name) {
        if ((!is_array($arr) && !is_object($arr)) || !isset($arr[$name]))
            return $default;

        $arr = $arr[$name];
    }

    return $arr;
}

/**
 * @param object $obj
 * @param array $trace
 * @param mixed $default
 *
 * @return mixed
 */
function objectGet(object $obj, array $trace, $default = null)
{
    foreach ($trace as $method => $args) {
        if (!is_object($obj))
            return $default;
        elseif (is_int($method))
            $obj = $obj->$args;
        else
            $obj = $obj->{$method}(...$args);
    }

    return $obj;
}

/**
 * <pre>
 * Accept-Language: ru;q=0.8,en-US,en;q=0.3,kz;q=0.5
 * ->
 * ['ru', 'kz', 'en']
 * </pre>
 *
 * @param string|null $header
 *
 * @return string[]
 */
function acceptLangs(?string $header = null): array
{
    if (is_null($header))
        $header = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? null;
    if (!$header)
        return [];

    $languages = [];
    parse_str(strtr($header, [';q=' => '=', ',' => '&']), $langs);
    arsort($langs);
    foreach ($langs as $lang => $q) {
        if (($lng = strstr($lang, '-', true)))
            $lang = $lng;
        $languages[] = strtolower($lang);
    }

    return array_unique($languages);
}
