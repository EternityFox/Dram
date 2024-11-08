<?php declare(strict_types=1);

namespace Core\Utils;

/**
 * ```php
 * var_dump(Assertion::check('==', 1, '1')); // true
 * var_dump(Assertion::check('same', 1, '1')); // true
 * var_dump(Assertion::check('=', 1, '1')); // false
 * var_dump(Assertion::check('equals', 1, '1')); // false
 * ```php
 */
class Assertion
{

    /**
     * @var array<string, string>
     */
    static protected array $assertAliases = [
        '=' => 'equals',
        '!=' => 'notEquals',
        '==' => 'same',
        '<>' => 'notSame',
        '>' => 'greaterThan',
        '<' => 'lessThan',
        '>=' => 'greaterOrEquals',
        '<=' => 'lessOrEquals',
        '&' => 'bitwiseAnd',
        ':' => 'typeof',
        '!:' => 'notTypeof',
    ];
    /**
     * @var array<string, string>
     */
    static protected array $types = [
        'null' => 'is_null',
        'bool' => 'is_bool',
        'boolean' => 'is_bool',
        'int' => 'is_int',
        'integer' => 'is_int',
        'float' => 'is_float',
        'real' => 'is_float',
        'double' => 'is_float',
        'numeric' => 'is_numeric',
        'string' => 'is_string',
        'array' => 'is_array',
        'iterable' => 'is_iterable',
        'countable' => 'is_countable',
        'resource' => 'is_resource',
        'callable' => 'is_callable',
        'obj' => 'is_object',
        'object' => 'is_object'
    ];

    /**
     * @param string $assert
     * @param mixed $value
     * @param mixed|null $expected
     *
     * @return bool
     */
    static public function check(
        string $assert, $value, $expected
    ): bool
    {
        if (isset(static::$assertAliases[$assert]))
            $assert = static::$assertAliases[$assert];

        return static::{"check{$assert}"}($value, $expected);
    }

    /**
     * @param mixed $value
     * @param mixed $expected
     *
     * @return bool
     */
    static public function checkEquals($value, $expected): bool
    {
        return $value === $expected;
    }

    /**
     * @param mixed $value
     * @param mixed $expected
     *
     * @return bool
     */
    static public function checkNotEquals($value, $expected): bool
    {
        return $value !== $expected;
    }

    /**
     * @param mixed $value
     * @param mixed $expected
     *
     * @return bool
     */
    static public function checkSame($value, $expected): bool
    {
        return $value == $expected;
    }

    /**
     * @param mixed $value
     * @param mixed $expected
     *
     * @return bool
     */
    static public function checkNotSame($value, $expected): bool
    {
        return $value != $expected;
    }

    /**
     * @param mixed $value1
     * @param mixed $value2
     *
     * @return bool
     */
    static public function checkGreaterThan($value1, $value2): bool
    {
        return $value1 > $value2;
    }

    /**
     * @param mixed $value1
     * @param mixed $value2
     *
     * @return bool
     */
    static public function checkGreaterOrEquals($value1, $value2): bool
    {
        return $value1 >= $value2;
    }

    /**
     * @param mixed $value1
     * @param mixed $value2
     *
     * @return bool
     */
    static public function checkLessThan($value1, $value2): bool
    {
        return $value1 < $value2;
    }

    /**
     * @param mixed $value1
     * @param mixed $value2
     *
     * @return bool
     */
    static public function checkLessOrEquals($value1, $value2): bool
    {
        return $value1 <= $value2;
    }

    /**
     * @param mixed $value
     * @param int $flags
     *
     * @return bool
     */
    static public function checkBitwiseAnd($value, int $flags): bool
    {
        return (bool) ($value & $flags);
    }

    /**
     * @param mixed $value
     * @param string $type
     *
     * @return bool
     */
    static public function checkTypeof($value, string $type): bool
    {
        return isset(static::$types[$type])
            ? (static::$types[$type])($value)
            : is_a($value, $type);
    }

    /**
     * @param mixed $value
     * @param string $type
     *
     * @return bool
     */
    static public function checkNotTypeof($value, string $type): bool
    {
        return isset(static::$types[$type])
            ? !(static::$types[$type])($value)
            : !is_a($value, $type);
    }

    /**
     * @param mixed $value
     * @param array $arr
     *
     * @return bool
     */
    static public function checkIn($value, array $arr): bool
    {
        return in_array($value, $arr);
    }

    /**
     * @param mixed $value
     * @param array $arr
     *
     * @return bool
     */
    static public function checkNotIn($value, array $arr): bool
    {
        return !in_array($value, $arr);
    }

}
