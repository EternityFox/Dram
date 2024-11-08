<?php declare(strict_types=1);

namespace Core\Http;

class Router
{

    /**
     * @var array
     */
    protected array $routes = [];

    /**
     * @param array|null $routes
     */
    public function __construct(?array $routes = null)
    {
        if ($routes)
            $this->routes = $routes;
    }

    /**
     * @param \Core\Http\ServerRequest $request
     *
     * @return array|null
     */
    public function handle(ServerRequest $request): ?array
    {
        return $this->find(($request->getTarget() ?: '/'));
    }

    /**
     * @param string $path
     * @param array|null $routes
     * @param string $prefix
     *
     * @return array|null
     */
    protected function find(
        string $path, ?array $routes = null, string $prefix = ''
    ): ?array
    {
        $routes ??= $this->routes;

        foreach ($routes as $pattern => $route) {
            if (is_array($route)) {
                if (0 !== strpos($path, "/{$prefix}{$pattern}"))
                    continue;

                if (($route = $this->find($path, $route, "{$prefix}{$pattern}/")))
                    return $route;
            }

            if (preg_match("~^/{$prefix}{$pattern}$~", $path, $matches)) {
                $params = [];
                break;
            }
        }

        if (!isset($params))
            return null;

        foreach ($matches as $name => $value) {
            if (!is_int($name))
                $params[$name] = $value;
        }

        return [$route, $params];
    }

}
