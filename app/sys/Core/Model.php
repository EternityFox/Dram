<?php declare(strict_types=1);

namespace Core;
use PDO,
    PDOStatement,
    ArrayAccess,
    ArrayIterator,
    Traversable,
    InvalidArgumentException,
    Exception;

/**
 * ```php
 * class User extends Model
 * {
 *
 *     static public function table(): string
 *     {
 *         return 'user';
 *     }
 *
 *     static public function pk(): string
 *     {
 *         return 'id';
 *     }
 *
 *     static public function struct(): array
 *     {
 *         return [
 *             'id' => self::FIELD_INT,
 *             'login' => self::FIELD_STRING,
 *             'is_admin' => self::FIELD_BOOL
 *         ];
 *     }
 *
 * }
 *
 * $userData = [
 *     'login' => 'JoinedUser',
 *     'is_admin' => false
 * ];
 * $user = new User($userData);
 * $user->save();
 * $id = $user->id;
 *
 * $user->login = 'RenamedUser';
 * $user->change(['login' => 'RenamedUser', 'is_admin' => false]);
 * $user->save();
 *
 * $user->remove();
 *
 * User::findOne('id = 1');
 * User::find(['login = ? AND is_admin = ?', ['RenamedUser', false]]);
 * User::find('is_admin = 1', ['id' => 'DESC'], [0, 10]);
 *
 * $where = 'id = 8'; // WHERE id = 8 (!!! Небезопасно)
 * // WHERE login = "UserName" AND is_admin = 0
 * $where = ['login' => 'UserName', 'admin' => 0];
 * $where = ['login = ? AND is_admin = ?', ['UserName', 0]];
 * $where = ['login = :login AND is_admin = :is_admin', [
 *     'login' => 'UserName',
 *     'is_admin' => 0
 * ]];
 *
 * $order = 'id'; // ORDER BY id
 * $order = ['id' => 'ASC']; // ORDER BY id ASC
 * $order = ['id', 'login']; // ORDER BY id, login
 * $order = ['id' => 'ASC', 'login' => 'DESC']; // ORDER BY id ASC, login DESC
 *
 * $limit = 1; // LIMIT 1
 * // LIMIT 1, 10
 * $limit = [1, 10];
 * $limit = '1, 10';
 *
 * // select, insert
 * $fields = 'login, is_admin';
 * $fields = ['login', 'is_admin'];
 * // insert, update
 * $fields = ['login' => 'UserName', 'is_admin' => 0];
 * // update
 * $fields = ['login = ?, is_admin = ?', ['UserName', 0]];
 * $fields = ['login = :login, is_admin = :is_admin', [
 *     'login' => 'UserName',
 *     'is_admin' => 0
 * ]];
 * $fields = 'login = "UserName", is_admin = 0'; // !!! Небезопасно
 *
 * $values = ['"UserName", 0', '"OtherUser", 0']; // !!! Небезопасно
 * $values = [['UserName', 0], ['AnotherUser', 0]];
 * $values = [
 *     ['login' => 'UserName', 'is_admin' => 0],
 *     ['is_admin' => 0, 'login' => 'AnotherUser']
 * ];
 */
abstract class Model implements ArrayAccess
{

    /**
     * Тип поля: string
     */
    const FIELD_STRING = 'string';
    /**
     * Тип поля: int
     */
    const FIELD_INT = 'int';
    /**
     * Тип поля: float
     */
    const FIELD_FLOAT = 'float';
    /**
     * Тип поля: bool
     */
    const FIELD_BOOL = 'bool';
    /**
     * Тип поля: json
     */
    const FIELD_JSON = 'json';
    /**
     * Тип поля: json массив
     */
    const FIELD_JSON_ARRAY = 'jsonArray';

    /**
     * @var array
     */
    protected array $fields = [];
    /**
     * @var array
     */
    protected array $changed_fields = [];
    /**
     * @var bool
     */
    protected bool $is_new_record = true;

    /**
     * @var PDO
     */
    static PDO $db;

    /**
     * @param array $fields
     * @param bool|null $is_new
     *
     * @throws \Exception
     */
    public function __construct(
        array $fields, ?bool $is_new = null
    )
    {
        if (($unknown = array_diff_key($fields, static::struct())))
            throw new Exception(implode(', ', $unknown));

        $this->fields = $fields;
        $this->is_new_record = is_null($is_new)
            ? !isset($fields[static::pk()]) : $is_new;
    }

    /**
     * @param string $name
     *
     * @return mixed
     * @throws \Exception
     */
    public function __get(string $name)
    {
        if (method_exists($this, "get{$name}"))
            return $this->{"get{$name}"}();
        elseif (empty(static::struct()[$name]))
            throw new Exception;

        return $this->fields[$name] ?? null;
    }

    /**
     * @param string $name
     * @param mixed $val
     *
     * @return void
     * @throws \Exception
     */
    public function __set(string $name, $val)
    {
        if (method_exists($this, "set{$name}"))
            return $this->{"set{$name}"}($val);
        elseif (empty(static::struct()[$name]))
            throw new Exception;

        $this->fields[$name] = $val;
        $this->changed_fields[$name] = true;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function __isset(string $name): bool
    {
        return array_key_exists($name, $this->fields);
    }

    /**
     * @return array
     */
    public function fields(): array
    {
        return $this->fields;
    }

    /**
     * @param array $fields
     *
     * @throws \Exception
     */
    public function change(array $fields)
    {
        foreach ($fields as $name => $val) {
            $this->__set($name, $val);
        }
    }

    /**
     * @throws \Exception
     */
    public function save()
    {
        if ($this->is_new_record) {
            $id = static::insert($this->fields);
            if (!isset($this->fields[static::pk()]))
                $this->fields[static::pk()] = $id;
            $this->is_new_record = false;
        } else {
            $fields = [];
            foreach ($this->changed_fields as $name => $null) {
                $fields[$name] = $this->fields[$name];
            }
            static::update($fields, [static::pk() => $this->fields[static::pk()]]);
        }

        $this->changed_fields = [];
    }

    /**
     * @throws \Exception
     */
    public function remove()
    {
        if ($this->is_new_record)
            throw new Exception;

        static::delete([static::pk() => $this->fields[static::pk()]]);
        unset($this->fields[static::pk()], $this->changed_fields[static::pk()]);
        $this->is_new_record = true;
    }

    /**
     * @param string|array|null $where
     * @param string|array|null $order
     *
     * @return static|null
     * @throws \Exception
     */
    static public function findOne($where = null, $order = null): ?self
    {
        $query = static::select('*', $where, $order, 1);
        if (!($res = $query->fetch()))
            return null;

        return new static(static::decodeFields($res), false);
    }

    /**
     * @param string|array|null $where
     * @param string|array|null $order
     * @param string|array|null $limit
     *
     * @return static[]
     * @throws \Exception
     */
    static public function find(
        $where = null, $order = null, $limit = null
    ): array
    {
        $list = [];
        $query = static::select('*', $where, $order, $limit);
        while ($res = $query->fetch()) {
            $list[] = new static(static::decodeFields($res), false);
        }

        return $list;
    }

    /**
     * @param string|array $fields
     * @param string|array|null $where
     * @param string|array|null $order
     * @param string|array|null $limit
     *
     * @return \PDOStatement
     * @throws \Exception
     */
    static public function select(
        $fields, $where = null, $order = null, $limit = null
    ): PDOStatement
    {
        if (is_array($fields)) {
            $fields = implode(', ', $fields);
        }
        $sql = "SELECT {$fields} FROM " . static::table();
        $params = static::finalizeSql($sql, $where, $order, $limit);

        return static::sendSql($sql, $params, true);
    }

    /**
     * @param string|array $fields
     * @param string|array|null $values
     *
     * @return int|string
     * @throws \Exception
     * @throws \InvalidArgumentException
     */
    static public function insert($fields, $values = null)
    {
        if (!$values) {
            if (!is_array($fields))
                throw new InvalidArgumentException;

            $getId = true;
            $fields = static::encodeFields($fields);
            $values = ['(' . implode(', ', $fields) . ')'];
            $fields = array_keys($fields);
        } else {
            $getId = false;
            if (is_string($fields))
                $fields = array_map('trim', explode(',', $fields));

            foreach ($values as $i => $vals) {
                if (is_array($vals)) {
                    $raw = [];
                    foreach ($fields as $pos => $name) {
                        $raw[$name] = array_key_exists($name, $vals)
                            ? $vals[$name] : $vals[$pos];
                    }
                    $vals = implode(', ', static::encodeFields($raw));
                }
                $values[$i] = "({$vals})";
            }
        }

        $sql = 'INSERT INTO ' . static::table()
               . ' (' . implode(', ', $fields) . ') VALUES '
               . implode(', ', $values);
        $res = static::sendSql($sql);

        if (!$getId)
            return $res;
        elseif (is_numeric(($id = static::$db->lastInsertId())))
            return (int) $id;
        else
            return $id;
    }

    /**
     * @param string|array $fields
     * @param string|array|null $where
     * @param string|array|null $order
     * @param string|array|null $limit
     *
     * @return int
     * @throws \Exception
     */
    static public function update(
        $fields, $where = null, $order = null, $limit = null
    ): int
    {
        if (is_array($fields)) {
            if (isset($fields[0])) {
                $params = $fields[1];
                $fields = $fields[0];
            } else {
                $fields = static::encodeFields($fields);
                array_walk($fields, function(&$val, $key) {
                    $val = "{$key} = {$val}";
                });
                $fields = implode(', ', $fields);
            }
        }

        $sql = 'UPDATE ' . static::table() . " SET {$fields}";
        $params = isset($params)
            ? array_merge(
                $params, static::finalizeSql($sql, $where, $order, $limit)
            ) : static::finalizeSql($sql, $where, $order, $limit);

        return static::sendSql($sql, $params);
    }

    /**
     * @param string|array|null $where
     * @param string|array|null $order
     * @param string|array|int|null $limit
     *
     * @return int
     * @throws \Exception
     */
    static public function delete(
        $where = null, $order = null, $limit = null
    ): int
    {
        $sql = 'DELETE FROM ' . static::table();
        $params = static::finalizeSql($sql, $where, $order, $limit);

        return static::sendSql($sql, $params);
    }

    /**
     * @param string $sql
     * @param array|null $params
     * @param bool $select
     *
     * @return \PDOStatement|int|bool
     */
    static protected function sendSql(
        string $sql, ?array $params = null, bool $select = false
    )
    {
        try {
            if ($params) {
                $sth = static::$db->prepare($sql);
                $sth->execute($params);

                return $select ? $sth : $sth->rowCount();
            }
            else {
                return $select
                    ? static::$db->query($sql)
                    : static::$db->exec($sql);
            }
        } catch(\PDOException $e) {
            var_dump($sql, $params);
            throw $e;
        }
    }

    /**
     * @param string $sql
     * @param string|array|null $where
     * @param string|array|null $order
     * @param string|array|null $limit
     *
     * @return array|null
     * @throws \Exception
     */
    static protected function finalizeSql(
        string &$sql, $where, $order, $limit
    ): ?array
    {
        $params = null;

        if ($where) {
            if (is_array($where)) {
                if (isset($where[0])) {
                    $params = $where[1];
                    $where = $where[0];
                } else {
                    $where = static::encodeFields($where);
                    array_walk($where, function(&$val, $key) {
                        $val = "{$key} = {$val}";
                    });
                    $where = implode(' AND ', $where);
                }
            }
            $sql .= " WHERE {$where}";
        }
        if ($order) {
            if (is_array($order)) {
                array_walk($order, function (&$val, $key) {
                    $val = is_string($key) ? "{$key} {$val}" : $val;
                });
                $order = implode(', ', $order);
            }
            $sql .= " ORDER BY {$order}";
        }
        if ($limit) {
            if (is_array($limit))
                $limit = $limit[0] . (isset($limit[1]) ? ", {$limit[1]}" : '');
            $sql .= " LIMIT {$limit}";
        }

        return $params;
    }

    /**
     * @param array $fields
     *
     * @return array
     * @throws \Exception
     */
    static public function decodeFields(array $fields): array
    {
        if (($unknown = array_diff_key($fields, static::struct())))
            throw new Exception(implode(', ', $unknown));

        foreach ($fields as $name => $val) {
            $decoder = 'decode' . static::struct()[$name];
            if (method_exists(__CLASS__, $decoder))
                $fields[$name] = static::{$decoder}($val);
            else
                settype($fields[$name], static::struct()[$name]);
        }

        return $fields;
    }

    /**
     * @param array $fields
     *
     * @return array
     * @throws \Exception
     */
    static public function encodeFields(array $fields): array
    {
        if (($unknown = array_diff_key($fields, static::struct())))
            throw new Exception(implode(', ', $unknown));

        foreach ($fields as $name => $val) {
            $encoder = 'encode' . static::struct()[$name];
            if (method_exists(__CLASS__, $encoder))
                $val = static::{$encoder}($val);
            else
                $val = (string) $val;
            $fields[$name] = $val;
        }

        return $fields;
    }

    /**
     * @param mixed $val
     *
     * @return string
     */
    static protected function encodeString($val): string
    {
        return static::$db->quote($val);
    }

    /**
     * @param mixed $val
     *
     * @return string
     */
    static protected function encodeBool($val): string
    {
        return $val ? '1' : '0';
    }

    /**
     * @param mixed $val
     *
     * @return string
     */
    static protected function encodeJson($val): string
    {
        return static::encodeString(json_encode($val, JSON_UNESCAPED_UNICODE));
    }

    /**
     * @param mixed $val
     *
     * @return mixed
     */
    static protected function decodeJson($val)
    {
        return json_decode($val);
    }

    /**
     * @param mixed $val
     *
     * @return string
     */
    static protected function encodeJsonArray($val): string
    {
        return static::encodeString(json_encode($val, JSON_UNESCAPED_UNICODE));
    }

    /**
     * @param mixed $val
     *
     * @return mixed
     */
    static protected function decodeJsonArray($val)
    {
        return json_decode($val, true);
    }

    /**
     * @param string $name
     *
     * @return mixed|null
     * @throws \Exception
     */
    public function offsetGet($name)
    {
        return $this->__get($name);
    }

    /**
     * @param string $name
     * @param mixed $val
     *
     * @throws \Exception
     */
    public function offsetSet($name, $val)
    {
        $this->__set($name, $val);
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function offsetExists($name)
    {
        return array_key_exists($name, $this->fields);
    }

    /**
     * @param string $name
     *
     * @throws \Exception
     */
    public function offsetUnset($name)
    {
        $this->__set($name, '');
    }

    /**
     * @return \Traversable
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->fields);
    }

    /**
     * @return string
     */
    static protected function pk(): string
    {
        return 'id';
    }

    /**
     * @return string
     */
    static protected function table(): string
    {
        $class = get_called_class();

        return strtolower(
            substr($class, (strrpos($class, '\\') + 1))
        );
    }

    /**
     * @return array
     */
    abstract static protected function struct(): array;

}

Model::$db = \App::get('db');
