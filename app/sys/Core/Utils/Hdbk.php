<?php declare(strict_types=1);

namespace Core\Utils;
use ArrayIterator,
    IteratorAggregate,
    Countable,
    Traversable,
    BadMethodCallException,
    Exception;

/**
 * ```php
 * // array[]
 * $users = new Hdbk(
 *     [
 *         1 => ['id' => 1,'name' => 'Admin', 'is_admin' => true],
 *         2 => ['id' => 2, 'name' => 'Moder', 'is_admin' => true],
 *         3 => ['id' => 3, 'name' => 'User1', 'is_admin' => false],
 *         4 => ['id' => 4, 'name' => 'User2', 'is_admin' => false]
 *     ]
 * );
 * // object[]
 * $users = new Hdbk(Users::find(), 'all');
 *
 * $users->has(1);
 * $users->has([0, 1, 2, 5]); // [1, 2]
 * $users->has([0, 1, 2, 5], false); // [0, 5]
 *
 * $users->get(1); // ['id' => 1,'name' => 'Admin', 'is_admin' => true]
 * $users->get([1, 2]); // [1 => [...], 2 => [...]]
 *
 * $users->push(5, ['id' => 5, 'name' => 'User3', 'is_admin' => false]);
 * $users->remove(5);
 * $users->push([5 => [...], 6 => [...], ...]);
 * $users->remove([5, 6, ...]);
 *
 * $users->all(); // [1 => [...], 2 => [...], ...]
 * $users->count(); // 4
 * count($users); // 4
 * $users->max('id'); // 4
 * $users->min('id'); // 1
 *
 * $users->column('name'); // [1 => 'Admin', 2 => 'Moder', ...]
 * $users->column('is_admin', 'name'); // ['Admin' => true, 'Moder' => true, ...]
 * $users->column(['id', 'is_admin'], 'name');
 * // [
 * //     'Admin' => ['id' => 1, 'is_admin' => true],
 * //     'Moder' => ['id' => 2, 'is_admin' => true],
 * //     ...
 * // ]
 *
 * $users->findOne($filter, $sort);
 * $users->find($filter, $sort, $limit);
 *
 * $filter = ['name' => 'Admin', 'is_admin' => true];
 * // name = admin and is_admin = true
 * $filter = ['name' => 'Admin', 'or', 'id' => 1];
 * // name = admin or id = 1
 * $filter = [
 *     ['id' => ['in', [1, 2]], 'or', 'is_admin' => true],
 *     'or', 'id' => ['<=', 2]
 * ];
 * // (id in(1, 2) or is_admin = true) or id <= 2
 *
 * $sort = 'id'; // id asc
 * $sort = ['id' => 'asc', 'name' => 'desc']; // id asc, name desc
 *
 * $limit = 1; // [1 => [...]]
 * $limit = [1, 2]; // [2 => [...], 3 => [...]]
 * $limit = -2; // [3 => [...], 4 => [...]]
 * $limit = [-2, 1]; // [3 => [...]]
 * ```php
 */
class Hdbk implements IteratorAggregate, Countable
{

    /**
     * @var array|null
     * @used-by filter()
     */
    static protected ?array $filter = null;

    /**
     * @var array
     */
    protected array $table = [];
    /**
     * @var string|null
     */
    protected ?string $converter = null;

    /**
     * @param array|null $table
     * @param string|null $converter
     */
    public function __construct(?array $table = null, ?string $converter = null)
    {
        if ($table)
            $this->table = $table;
        if ($converter)
            $this->converter = $converter;
    }

    /**
     * @param mixed $key
     * @param bool $take_exists
     *
     * @return bool|array
     */
    public function has($key, bool $take_exists = true)
    {
        if (is_array($key))
            return $take_exists
                ? array_keys(array_intersect_key(array_flip($key), $this->table))
                : array_keys(array_diff_key(array_flip($key), $this->table));
        else
            return array_key_exists($key, $this->table);
    }

    /**
     * @param mixed $key
     *
     * @return mixed|null
     */
    public function get($key)
    {
        if (is_array($key)) {
            $keys = array_flip($key);
            $values = array_intersect_key($this->table, $keys);
            foreach ($keys as $key => $val) {
                if (!isset($values[$key])) {
                    unset($keys[$key]);
                    continue;
                }
                $keys[$key] = $values[$key];
            }

            return $keys;
        } else {
            return $this->table[$key] ?? null;
        }
    }

    /**
     * @param mixed $key
     * @param array|object|null $value
     *
     * @throws \BadMethodCallException
     */
    public function push($key, $value = null)
    {
        if (is_array($key))
            $this->table = $key + $this->table;
        elseif (!$value)
            throw new BadMethodCallException;
        else
            $this->table[$key] = $value;
    }

    /**
     * @param mixed $key
     */
    public function remove($key)
    {
        if (is_array($key))
            $this->table = array_diff_key($this->table, array_flip($key));
        else
            unset($this->table[$key]);
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->table);
    }

    /**
     * @return \Traversable
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->table);
    }

    /**
     * @param string $field
     *
     * @return mixed|null
     */
    public function max(string $field)
    {
        $vals = array_column($this->table, $field);
        return $vals ? max($vals) : null;
    }

    /**
     * @param string $field
     *
     * @return mixed|null
     */
    public function min(string $field)
    {
        $vals = array_column($this->table, $field);
        return $vals ? min($vals) : null;
    }

    /**
     * @param string|array $field
     * @param string|null $key
     *
     * @return array
     */
    public function column($field, ?string $key = null): array
    {
        $table = [];

        if ($key) {
            if (!is_array($field))
               return array_column($this->table, $field, $key);

            $field = array_flip($field);
            foreach ($this->table as $values) {
                if (isset($values[$key])
                    && ($vals = $this->intersect_key($values, $field))
                )
                    $table[$values[$key]] = $vals;
            }
        } elseif (is_array($field)) {
            $field = array_flip($field);
            foreach ($this->table as $key => $values) {
                if (($vals = $this->intersect_key($values, $field)))
                    $table[$key] = $vals;
            }
        } else {
            foreach ($this->table as $key => $values) {
                if ($this->key_exists($field, $values))
                    $table[$key] = $values[$field];
            }
        }

        return $table;
    }

    /**
     * @param string|array|null $sort
     * @param int|array|null $limit
     *
     * @return array
     */
    public function all($sort = null, $limit = null): array
    {
        $table = $this->table;
        if ($sort)
            static::sort($table, $sort);

        return $limit ? static::slice($table, $limit) : $table;
    }

    /**
     * @param array $filter
     * @param string|array|null $sort
     *
     * @return array|object|null
     */
    public function findOne(array $filter, $sort = null)
    {
        if (!($table = static::filter($this->table, $filter)))
            return null;
        elseif ($sort)
            static::sort($table, $sort);

        return $table[array_key_first($table)];
    }

    /**
     * @param array $filter
     * @param string|array|null $sort
     * @param int|int[]|null $limit
     *
     * @return array
     */
    public function find(array $filter, $sort = null, $limit = null): array
    {
        if (!($table = static::filter($this->table, $filter)))
            return [];
        elseif ($sort)
            static::sort($table, $sort);

        if (!$limit) {
            return $table;
        } elseif (is_array($limit)) {
            if (2 === count($limit))
                return static::slice($table, ...$limit);
            $limit = array_shift($limit);
        }

        return (0 > $limit)
            ? static::slice($table, $limit)
            : static::slice($table, 0, $limit);
    }

    /**
     * @param array|null $filter
     * @param string|array|null $sort
     * @param int|int[]|null $limit
     *
     * @return static
     */
    public function new(
        ?array $filter = null, $sort = null, $limit = null
    ): self
    {
        return new static($this->find($filter, $sort, $limit));
    }

    /**
     * @param array $table
     * @param array $filter
     *
     * @return array
     */
    static function filter(array $table, array $filter): array
    {
        static::$filter = $filter;
        $table = array_filter($table, [__CLASS__, 'filterHelper']);
        static::$filter = null;

        return $table;
    }

    /**
     * @param array $table
     * @param string|array $sort
     */
    static function sort(array &$table, $sort)
    {
        if (!is_array($sort))
            $sort = [$sort];

        $args = [];
        foreach ($sort as $field => $type) {
            if (is_int($field))
                [$field, $type] = [$type, SORT_ASC];
            else
                $type = ('desc' === strtolower($type)) ? SORT_DESC : SORT_ASC;

            if (!($arr = array_column($table, $field)))
                continue;
            $args[] = $arr;
            $args[] = $type;
        }

        $args[] = &$table;
        array_multisort(...$args);
    }

    /**
     * @param array $table
     * @param int $start
     * @param int|null $limit
     *
     * @return array
     */
    static function slice(
        array $table, int $start, ?int $limit = null
    ): array
    {
        return array_slice($table, $start, $limit, true);
    }

    /**
     * @param array|object $values
     * @param array|null $filter
     *
     * @return bool
     * @throws \Exception
     */
    static function filterHelper($values, ?array $filter = null): bool
    {
        if (!$filter) {
            if (!static::$filter)
                return true;
            else
                $filter = static::$filter;
        }

        return static::checkFilter($values, $filter);
    }

    /**
     * @param array|object $values
     * @param array $filter
     *
     * @return bool
     * @throws \Exception
     */
    static protected function checkFilter($values, array $filter): bool
    {
        $res = null;
        $context = 0;

        foreach ($filter as $field => $cond) {
            if (is_string($field)) {
                if (!is_array($cond))
                    $args = ['=', ($values[$field] ?? null), $cond];
                elseif (isset($cond[1]))
                    $args = [$cond[0], ($values[$field] ?? null), $cond[1]];
                else
                    $args = [$cond[0], ($values[$field] ?? null)];

                $curr = Assertion::check(...$args);
            } elseif (is_int($field)) {
                if (!is_string($cond)) {
                    $curr = static::checkFilter($values, $cond);
                } else {
                    $or_or_and = strtolower($cond);
                    if ('or' === $or_or_and) {
                        $context = 1;
                        continue;
                    } elseif ('and' === $or_or_and) {
                        $context = 2;
                        continue;
                    }
                }
            } else {
                throw new Exception;
            }

            if (!is_null($res) && !$context)
                $context = 2;

            if ($context)
                $res = (1 === $context) ? ($res || $curr) : ($res && $curr);
            elseif (false === $res)
                return false;
            else
                $res = $curr;
        }

        return $res;
    }

    /**
     * @return array
     */
    protected function intersect_key(): array
    {
        if (!$this->converter)
            return array_intersect_key(...func_get_args());

        $args = func_get_args();
        $args[0] = $args[0]->{$this->converter}();

        return array_intersect_key(...$args);
    }

    /**
     * @param mixed $key
     * @param array|object $values
     *
     * @return bool
     */
    protected function key_exists($key, $values): bool
    {
        return $this->converter
            ? isset($values[$key]) : array_key_exists($key, $values);
    }

}
