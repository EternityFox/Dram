<?php declare(strict_types=1);

namespace Core\Utils;

/**
 * Короткое представление:
 * <pre>
 * null: NULL
 * bool: TRUE|FALSE
 * int: 1
 * float: 1.0
 * string: "string <= 50 chars..."
 * array: array
 * object: ClassName
 * resource: stream#2
 * unknown type: unknown
 * </pre>
 *
 * Расширенное представление:
 * <pre>
 * string: "full string"
 * array:
 * [
 *     0 => NULL,
 *     1 => [
 *         "class" => ClassName(
 *             "property1" => "value",
 *             "property2" => [ ... ],
 *             ...
 *         )
 *     ],
 *     ...
 * ]
 * object:
 * ClassName(
 *     "property1" => "value",
 *     "property2" => [ ... ],
 *     ...
 * )
 * </pre>
 */
class Stringify
{

    /**
     * Кол-во символов в строке при коротком представлении
     */
    const STRING_SHORT_LEN = 50;
    /**
     * Символ переноса строк по умолчанию
     */
    const DEFAULT_EOL = self::EOL_FS;

    /**
     * Короткое представление
     */
    const SHORT_FORMAT = 1;
    /**
     * Символ переноса строк: используемый ФС
     */
    const EOL_FS = 2;
    /**
     * Символ переноса строк: \n
     */
    const EOL_UNIX = 4;
    /**
     * Символ переноса строк: \r\n
     */
    const EOL_WIN = 8;
    /**
     * Символ переноса строк: <br>
     */
    const EOL_HTML = 16;
    /**
     * Короткое представление вложенных данных
     */
    const SHORT_CHILDRENS = 32;

    /**
     * @var array
     */
    static protected array $eols = [
        self::EOL_FS => PHP_EOL,
        self::EOL_UNIX => "\n",
        self::EOL_WIN => "\r\n",
        self::EOL_HTML => '<br>'
    ];
    /**
     * @var string
     */
    static protected string $prefix = '';

    /**
     * @param mixed $value
     * @param int $flags
     *
     * @return string
     */
    static function from($value, int $flags = 0): string
    {
        $aliases = [
            'resource (closed)' => 'resource',
            'unknown type' => 'unknown'
        ];

        $type = gettype($value);
        if (isset($aliases[$type]))
            $type = $aliases[$type];

        return static::{"format{$type}"}($value, $flags);
    }

    /**
     * @param mixed $value
     *
     * @return string
     */
    static function short($value): string
    {
        return static::from($value, self::SHORT_FORMAT);
    }

    /**
     * @return string
     */
    static protected function formatNULL(): string
    {
        return 'NULL';
    }

    /**
     * @param bool $value
     *
     * @return string
     */
    static protected function formatBoolean(bool $value): string
    {
        return $value ? 'TRUE' : 'FALSE';
    }

    /**
     * @param int $value
     *
     * @return string
     */
    static protected function formatInteger(int $value): string
    {
        return (string) $value;
    }

    /**
     * @param float $value
     *
     * @return string
     */
    static protected function formatDouble(float $value): string
    {
        $str = rtrim(sprintf("%f", $value), '0');
        return ('.' !== $str[-1]) ? $str : $str . '0';
    }

    /**
     * @param resource $value
     *
     * @return string
     */
    static protected function formatResource($value): string
    {
        return get_resource_type($value) . strrchr((string) $value, '#');
    }

    /**
     * @return string
     */
    static protected function formatUnknown(): string
    {
        return 'unknown';
    }

    /**
     * @param string $value
     * @param int $flags
     *
     * @return string
     */
    static protected function formatString(string $value, int $flags = 0): string
    {
        if ((self::SHORT_FORMAT & $flags ||
             (static::$prefix && self::SHORT_CHILDRENS & $flags)
            ) && strlen($value) > self::STRING_SHORT_LEN
        )
            $value = substr($value, 0, (self::STRING_SHORT_LEN - 3)) . '...';

        return "\"{$value}\"";
    }

    /**
     * @param array $value
     * @param int $flags
     *
     * @return string
     */
    static protected function formatArray(array $value, int $flags = 0): string
    {
        if (self::SHORT_FORMAT & $flags
            || (static::$prefix && self::SHORT_CHILDRENS & $flags)
        )
            return 'array';
        elseif (!$value)
            return '[]';

        $eol = static::$eols[
            ((self::EOL_FS|self::EOL_UNIX|self::EOL_WIN) & $flags)
                ?: self::DEFAULT_EOL
        ];
        if (self::EOL_HTML & $flags)
            $eol .= static::$eols[self::EOL_HTML];
        $eol .= static::$prefix;

        $str = "[{$eol}";
        static::addChildrens($value, $flags, $eol, $str);

        return (static::$prefix ? substr($str, 0, -4) : $str) . ']';
    }

    /**
     * @param object $value
     * @param int $flags
     *
     * @return string
     */
    static protected function formatObject($value, int $flags = 0): string
    {
        if (self::SHORT_FORMAT & $flags
            || (static::$prefix && self::SHORT_CHILDRENS & $flags)
        )
            return get_class($value);

        $eol = static::$eols[
               ((self::EOL_FS|self::EOL_UNIX|self::EOL_WIN) & $flags)
                   ?: self::DEFAULT_EOL
        ];
        if (self::EOL_HTML & $flags)
            $eol .= static::$eols[self::EOL_HTML];
        $eol .= static::$prefix;

        if (0 === strpos(($class = get_class($value)), 'class@anonymous'))
            $class = 'class@anonymous';
        $str = "{$class}({$eol}";
        $arr = method_exists($value, '__debugInfo')
            ? $value->__debugInfo() : get_object_vars($value);
        static::addChildrens($arr, $flags, $eol, $str);

        return (static::$prefix ? substr($str, 0, -4) : $str) . ')';
    }

    /**
     * @param array $arr
     * @param int $flags
     * @param string $eol
     * @param string $str
     */
    static protected function addChildrens(
        array $arr, int $flags, string $eol, string &$str
    )
    {
        foreach ($arr as $key => $val) {
            $str .= static::short($key) . ' => ';
            if (self::SHORT_CHILDRENS & $flags) {
                $str .= static::short($val) . $eol;
                continue;
            }

            $old = static::$prefix;
            static::$prefix .= '    ';
            $str .= static::from($val, $flags) . ",{$eol}";
            static::$prefix = $old;
        }
    }

}
