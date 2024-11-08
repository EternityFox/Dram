<?php declare(strict_types=1);

namespace Core;
use Core\Http\ServerRequest,
    Core\Http\Router,
    Core\Utils\Stringify,
    Closure,
    Exception,
    LogicException;

class App
{

    /**
     * Директория приложения
     */
    const DIR = APP_DIR;
    /**
     * Время инициализации приложения
     */
    const STARTED_AT = STARTED_AT;
    /**
     * Метка текущего времени (timestamp)
     */
    const TIME = TIME;

    /**
     * Конфигурационные данные
     * @var array<string, mixed>
     */
    static protected array $configure = [];
    /**
     * Компоненты
     * @var array<string, object>
     */
    static protected array $components = [];
    /**
     * Основные пути ФС
     * @var array<string, string>
     */
    static protected array $paths = [];
    /**
     * Маршрут по умолчанию
     * @var string|\Closure
     */
    static public $defaultRoute = 'site/index';
    /**
     * Пространство имён контроллеров
     * @var string
     */
    static public string $controllerNamespace = 'App\Controller';
    /**
     * Текущий маршрут
     * @var string|\Closure
     */
    static public $route = null;

    /**
     * @param array<string, mixed>|null $config
     *
     * @throws \LogicException
     */
    public function __construct(?array $config = null)
    {
        if (class_exists('App'))
            throw new LogicException('Application already exists');
        class_alias(get_called_class(), 'App');

        if (!$config)
            return;

        foreach ($config as $name => $value) {
            if (method_exists($this, "set{$name}"))
                $this->{"set{$name}"}($value);
            elseif (property_exists($this, $name))
                static::$$name = $value;
            else
                static::setConfig($name, $value);
        }
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    static public function config(string $name)
    {
        if (!strpos($name, '>'))
           return static::$configure[$name] ?? null;

        $trace = explode('>', $name);
        $last = array_key_last($trace);
        $data = static::$configure;
        foreach ($trace as $i => $name) {
            if (!isset($data[$name]))
                return null;
            elseif ($last === $i)
                return $data[$name];
            elseif (!is_array($data[$name]))
                return null;

            $data = $data[$name];
        }
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    static public function setConfig(string $name, $value)
    {
        static::$configure[$name] = $value;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    static public function has(string $name): bool
    {
        return isset(static::$components[$name]);
    }

    /**
     * @param string $name
     *
     * @return object
     * @throws \Exception
     */
    static public function get(string $name): object
    {
        if (isset(static::$components[$name]))
            return static::$components[$name];
        elseif (isset(static::$configure[$name])
                && is_callable(static::$configure[$name])
        )
            $cmp = (static::$configure[$name])();
        elseif (method_exists(get_called_class(), "create{$name}"))
            $cmp = static::{"create{$name}"}();
        else
            throw new Exception("Component \"{$name}\" not found");

        return static::$components[$name] = $cmp;
    }

    /**
     * @param string $path
     *
     * @return string
     */
    static public function path(string $path): string
    {
        $paths = explode(DIRECTORY_SEPARATOR, handleDS($path));

        if ('app' === $paths[0])
            $paths[0] = self::DIR;
        elseif (isset(static::$paths[$paths[0]]))
            $paths[0] = static::$paths[$paths[0]];

        return implode(DIRECTORY_SEPARATOR, $paths);
    }

    /**
     * @param string $name
     * @param string $value
     */
    static public function setPath(string $name, string $value)
    {
        static::$paths[$name] = rtrim(static::path($value), DIRECTORY_SEPARATOR);
    }

    /**
     * @param array<string, string> $paths
     */
    static public function setPaths(array $paths)
    {
        foreach ($paths as $name => $path) {
            static::setPath($name, $path);
        }
    }

    /**
     * @return \Core\Http\ServerRequest
     */
    static protected function createRequest()
    {
        return new ServerRequest;
    }

    /**
     * @return \Core\Viewer
     */
    static protected function createViewer()
    {
        return new Viewer(static::path('view'));
    }

    /**
     * @param \Core\Http\ServerRequest|null $request
     *
     * @throws \Exception
     */
    public function run(?ServerRequest $request = null)
    {
        if ($request)
            static::$components['request'] = $request;
        else
            $request ??= static::get('request');

        if (($response = static::runRequest($request)))
            static::handleResponse($response);
    }

    /**
     * @param \Core\Http\ServerRequest $request
     *
     * @return array|null
     * @throws \Exception
     */
    static public function runRequest(ServerRequest $request): ?array
    {
        $action = static::handleRequest($request);

        return $action();
    }

    /**
     * @param string $route
     * @param array|null $args
     * @param \Core\Http\ServerRequest|null $request
     *
     * @return array|null
     * @throws \Exception
     */
    static public function runRoute(
        string $route, ?array $args = null, ?ServerRequest $request = null
    ): ?array
    {
        $action = static::handleRoute(
            $route, ($args ?? []), ($request ?? static::get('request'))
        );

        return $action();
    }

    /**
     * @param \Core\Http\ServerRequest $request
     *
     * @return \Closure
     * @throws \Exception
     */
    static protected function handleRequest(ServerRequest $request): Closure
    {
        if (!($route = static::get('router')->handle($request)))
            $route = [static::$defaultRoute, []];
        elseif (!is_array($route))
            $route = [$route, []];
        elseif (1 === count($route))
            $route[] = [];
        elseif (2 < count($route))
            throw new Exception('Invalid route data: ' . Stringify::from($route));

        $route[] = $request;
        return static::handleRoute(...$route);
    }

    /**
     * @param string|\Closure $route
     * @param array $args
     * @param \Core\Http\ServerRequest $request
     *
     * @return Closure
     * @throws \Exception
     */
    static protected function handleRoute(
        $route, array $args, ServerRequest $request
    ): Closure
    {
        if (is_object($route) && $route instanceof Closure) {
            static::$route = $route;
            return fn() => $route($args);
        } elseif (!is_string($route)) {
            throw new Exception('Invalid route: ' . Stringify::from($route));
        }

        $controller = static::createController($route, $request);
        $action = substr($route, (strrpos($route, '/') + 1));
        static::$route = $route;
        static::$components['controller'] = $controller;

        return fn() => $controller->run($action, $args);
    }

    /**
     * @param string $route
     * @param \Core\Http\ServerRequest $request
     *
     * @return \Core\Controller
     */
    static protected function createController(
        string $route, ServerRequest $request
    ): Controller
    {
        $class = static::$controllerNamespace
                 . '\\' . ucfirst(substr($route, 0, strrpos($route, '/')))
                 . 'Controller';

        return new $class($request);
    }

    /**
     * @param array $response
     *
     * @throws \Exception
     */
    static protected function handleResponse(array $response)
    {
        static::get('viewer')->render(...$response);
    }

}
