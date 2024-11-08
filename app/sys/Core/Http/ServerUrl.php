<?php declare(strict_types=1);

namespace Core\Http;

class ServerUrl
{

    /**
     * @var string
     */
    static protected string $defaultQuerySeparator = '&';
    /**
     * @var int
     */
    static protected int $defaultQueryEncType = PHP_QUERY_RFC3986;

    /**
     * @var string
     */
    protected string $scheme;
    /**
     * @var string
     */
    protected string $user = '';
    /**
     * @var string
     */
    protected string $pass = '';
    /**
     * @var string
     */
    protected string $host;
    /**
     * @var int|null
     */
    protected ?int $port = null;
    /**
     * @var string
     */
    protected string $path = '/';
    /**
     * @var array
     */
    protected array $queryParams = [];

    /**
     * @param string $url
     */
    public function __construct(string $url)
    {
        $parts = parse_url($url);

        if (isset($parts['query'])) {
            parse_str($parts['query'], $this->queryParams);
            unset($parts['query']);
        }

        foreach ($parts as $name => $val) {
            $this->{$name} = $val;
        }
    }

    /**
     * @return string
     */
    public function getScheme(): string
    {
        return $this->scheme;
    }

    /**
     * @return string
     */
    public function getUser(): string
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getPass(): string
    {
        return $this->pass;
    }

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @return int|null
     */
    public function getPort(): ?int
    {
        return $this->port;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @param string|null $sep
     * @param int|null $enc_type
     *
     * @return string
     */
    public function getQuery(
        ?string $sep = null, ?int $enc_type = null
    ): string
    {
        if (!$sep)
            $sep = static::$defaultQuerySeparator;
        if (!$enc_type)
            $enc_type = static::$defaultQueryEncType;

        return $this->queryParams
            ? http_build_query($this->queryParams, '', $sep, $enc_type)
            : '';
    }

    /**
     * @return array
     */
    public function getQueryParams(): array
    {
        return $this->queryParams;
    }

    /**
     * @param string|null $name
     *
     * @return string|array|null
     */
    public function getQueryParam(string $name)
    {
        return $this->queryParams[$name] ?? null;
    }

    /**
     * @return string
     */
    public function getBaseUrl(): string
    {
        return "{$this->scheme}://{$this->host}";
    }

    /**
     * @return string
     */
    public function getUserInfo(): string
    {
        return $this->pass ? "{$this->user}:{$this->pass}" : $this->user;
    }

    /**
     * @return string
     */
    public function getAuthority(): string
    {
        return "//{$this->getUserInfo()}{$this->host}"
               . ($this->port ? ":{$this->port}" : '');
    }

    /**
     * @return string
     */
    public function getHierPart(): string
    {
        return "{$this->getAuthority()}{$this->path}";
    }

    /**
     * @param string|null $query_sep
     * @param int|null $query_enc_type
     *
     * @return string
     */
    public function getRelativeRef(
        ?string $query_sep = null, ?int $query_enc_type = null
    ): string
    {
        return "{$this->path}" . ($this->queryParams
                ? "?{$this->getQuery($query_sep, $query_enc_type)}" : '');
    }

    /**
     * @param string|null $query_sep
     * @param int|null $query_enc_type
     *
     * @return string
     */
    public function getAbsoluteUrl(
        ?string $query_sep = null, ?int $query_enc_type = null
    ): string
    {
        return "{$this->scheme}:{$this->getAuthority()}"
               . $this->getRelativeRef($query_sep, $query_enc_type);
    }

    /**
     * @return bool
     */
    public function isDefaultPort(): bool
    {
        return (!$this->port || static::checkPortOnDefault($this->port));
    }

    /**
     * @return bool
     */
    public function isSecure(): bool
    {
        return ('https' === $this->scheme);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getAbsoluteUrl();
    }

    /**
     * @param int $port
     *
     * @return bool
     */
    static function checkPortOnDefault(int $port): bool
    {
        return (80 === $port || 443 === $port);
    }

    /**
     * @param array|null $server
     *
     * @return static
     */
    static function create(?array $server = null): self
    {
        if (!$server)
            $server = $_SERVER;

        if ('cli' === PHP_SAPI) {
            if (isset($server['PHP_SELF'])
                && '/' !== $server['PHP_SELF'][0]
            )
                $server['PHP_SELF'] = "/{$server['PHP_SELF']}";
            elseif (isset($server['SCRIPT_NAME'])
                    && '/' !== $server['SCRIPT_NAME'][0]
            )
                $server['SCRIPT_NAME'] = "/{$server['SCRIPT_NAME']}";
        }

        return new static(
            ($server['REQUEST_SCHEME']
             ?? ((isset($server['HTTPS'])
                  && 'off' !== strtolower($server['HTTPS'])
                 ) || (isset($server['SERVER_PORT'])
                     && 443 == $server['SERVER_PORT']
                 ) ? 'https' : 'http')
            ) . '://'
            . ($server['HTTP_HOST']
               ?? $server['SERVER_NAME']
                  ?? $server['SERVER_ADDR']
                     ?? 'localhost'
            ) . ((isset($server['SERVER_PORT'])
                 && !static::checkPortOnDefault((int) $server['SERVER_PORT'])
            ) ? ":{$server['SERVER_PORT']}" : '')
            . ($server['REQUEST_URI']
                 ?? ($server['PHP_SELF'] ?? $server['SCRIPT_NAME'] ?? '/')
                    . (isset($server['QUERY_STRING'])
                    ? "?{$server['QUERY_STRING']}" : '')
            )
        );
    }

}
