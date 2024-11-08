<?php declare(strict_types=1);

namespace Core\Http;

class ServerRequest
{

    /**
     * @var \Core\Http\ServerUrl
     */
    protected ServerUrl $url;
    /**
     * @var string
     */
    protected string $method = 'GET';
    /**
     * @var float
     */
    protected float $protocolVersion = 1.1;
    /**
     * @var array
     */
    protected array $headers = [];
    /**
     * @var array
     */
    protected array $cookies = [];
    /**
     * @var array
     */
    protected array $get = [];
    /**
     * @var array
     */
    protected array $post = [];
    /**
     * @var array
     */
    protected array $server = [];

    /**
     * @var array
     */
    protected array $defaultCookieOptions = [
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax'
    ];

    /**
     * @param \Core\Http\ServerUrl|null $url
     * @param array|null $server
     * @param array|null $post
     */
    public function __construct(
        ?ServerUrl $url = null, ?array $server = null, ?array $post = null
    )
    {
        $server ??= $_SERVER;
        $post ??= $_POST;

        $this->url = $url ?? ServerUrl::create($server);
        $this->server = $server;
        $this->post = $post;

        // if ('cli' !== PHP_SAPI && $server['argc'] && isset($server['argv'][0]))
        //     parse_str($server['argv'][0], $this->get);

        if (isset($server['REQUEST_METHOD']))
            $this->method = strtoupper($server['REQUEST_METHOD']);
        if (isset($server['SERVER_PROTOCOL']))
            $this->protocolVersion = floatval(
                substr(strrchr($server['SERVER_PROTOCOL'], '/'), 1)
            );

        $this->headers = static::takeHeaders($server);
        if (isset($this->headers['cookie']))
            parse_str(
                strtr($this->headers['cookie'], ['; ' => '&']), $this->cookies
            );

        $this->defaultCookieOptions['domain'] = ".{$this->url->getHost()}";
        $this->defaultCookieOptions['secure'] = $this->isSecure();
        $this->defaultCookieOptions['expires'] = time() + 60*60*24*31;
    }

    /**
     * @return \Core\Http\ServerUrl
     */
    public function getUrl(): ServerUrl
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return string
     */
    public function getTarget(): string
    {
        return $this->url->getRelativeRef('&', PHP_QUERY_RFC3986);
    }

    /**
     * @return float
     */
    public function getProtocolVersion(): float
    {
        return $this->protocolVersion;
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @param string $name
     *
     * @return string|null
     */
    public function getHeader(string $name): ?string
    {
        return $this->headers[$name] ?? null;
    }

    /**
     * @param string $name
     *
     * @return array
     */
    public function getHeaderValues(string $name): array
    {
        if (empty($this->headers[$name]))
            return [];
        elseif (!is_array($this->headers[$name]))
            return [$this->headers[$name]];
        else
            return $this->headers[$name];
    }

    /**
     * @return array
     */
    public function getCookies(): array
    {
        return $this->cookies;
    }

    /**
     * @param string $name
     *
     * @return string|null
     */
    public function getCookie(string $name): ?string
    {
        return $this->cookies[$name] ?? null;
    }

    /**
     * @param string $name
     * @param mixed|null $val
     * @param array|null $options
     */
    public function setCookie(string $name, $val = null, ?array $options = null)
    {
        $options = $options
            ? ($options + $this->defaultCookieOptions)
            : $this->defaultCookieOptions;

        if (is_null($val))
            $options['expires'] = time() - 86400;

        $this->cookies[$name] = $val;
        setcookie($name, (string) $val, $options);
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return $this->url->getQueryParams();
    }

    /**
     * @param string $name
     *
     * @return string|array|null
     */
    public function get(string $name)
    {
        return $this->get[$name] ?? $this->url->getQueryParam($name);
    }

    /**
     * @return array
     */
    public function postParams(): array
    {
        return $this->post;
    }

    /**
     * @param string $name
     *
     * @return string|array|null
     */
    public function post(string $name)
    {
        return $this->post[$name] ?? null;
    }

    /**
     * @return array
     */
    public function envParams(): array
    {
        return $this->server;
    }

    /**
     * @param string $name
     *
     * @return mixed|null
     */
    public function env(string $name)
    {
        return $this->server[$name] ?? null;
    }

    /**
     * @return bool
     */
    public function isSecure(): bool
    {
        return $this->url->isSecure();
    }

    /**
     * @return bool
     */
    public function isAjax(): bool
    {
        if (!($header = $this->getHeader('x-requested-with')))
            return false;
        return ('xmlhttprequest' === strtolower($header));
    }

    /**
     * @param array $server
     *
     * @return array
     */
    static public function takeHeaders(array $server): array
    {
        $headers = [];
        foreach ($server as $name => $val) {
            if (0 === strpos($name, 'HTTP_'))
                $headers[strtolower(strtr(substr($name, 5), ['_' => '-']))]
                    = $val;
        }

        return $headers;
    }

}
