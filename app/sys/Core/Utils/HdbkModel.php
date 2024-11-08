<?php declare(strict_types=1);

namespace Core\Utils;

/**
 * ```php
 * class Admin extends User
 * {
 *
 *     static protected function handbook(): array
 *     {
 *         return [
 *             'index' => 'id',
 *             'fields' => '*',
 *             'filter' => 'is_admin = 1'
 *         ];
 *     }
 *
 * }
 * ```php
 *
 * @see Hdbk
 */
trait HdbkModel
{

    /**
     * @var Hdbk|null
     */
    static protected ?Hdbk $hdbk = null;

    /**
     * @return Hdbk
     */
    static public function hdbk(): Hdbk
    {
        if (!static::$hdbk) {
            $args = [];
            $names = ['index', 'fields', 'filter', 'sort', 'limit'];
            foreach ($names as $name) {
                $args[] = static::handbook()[$name] ?? null;
            }

            static::$hdbk = static::createHdbk(...$args);
        }

        return static::$hdbk;
    }

    /**
     * @param string $index
     * @param array|null $fields
     * @param string|array|null $filter
     * @param string|array|null $sort
     * @param string|array|null $limit
     *
     * @return Hdbk
     */
    static public function createHdbk(
        string $index,
        $fields = null,
        $filter = null,
        $sort = null,
        $limit = null
    ): Hdbk
    {
        if (is_null($fields)) {
            $fields = '*';
            $type = 0;
        } else {
            if (!in_array($index, $fields)) {
                $type = 2;
                $fields[] = $index;
            } else {
                $type = 1;
            }
        }

        $table = [];
        $query = static::select($fields, $filter, $sort, $limit);
        while ($res = $query->fetch()) {
            $key = $res[$index];

            if (0 === $type)
                $res = new static(static::decodeFields($res), false);
            elseif (2 === $type)
                unset($res[$index]);

            $table[$key] = $res;
        }

        return new Hdbk($table, ($type ? null : 'fields'));
    }

    /**
     * @return array
     */
    static protected function handbook(): array
    {
        return [
            'index' => static::pk(),
            'fields' => '*'
        ];
    }

}
