<?php declare(strict_types=1);

namespace Core;

/**
 * ```php
 * // lang/en/
 * //     phrases.php
 * //     about.txt
 * //     docs.txt
 * // lang/ru/
 * //     main.php
 * //     days.php
 * //     months.php
 * //     about.txt
 *
 * $lang = new Lang(__DIR__ . '/lang/ru', 'ru');
 * $lang->load(['name' => 'Название']);
 * $lang->load('main');
 * $lang->load('days', 'days');
 * $lang->loadFile($lang->getDir() . '/months.php', 'months');
 *
 * $name = $lang->get('name');
 * $jul = $lang->get('months>short>Jul');
 * $formatted = $lang->get('more +%d items (of %d)', 2, 10);
 *
 * $months = $lang->params('months>short');
 *
 * Lang::$defaultLang = 'en';
 * Lang::$defaultDir = dirname($lang->getDir()) . '/en';
 * $phrases = $lang->file('phrases'); // lang/en/phrases.php
 * $content = $lang->content('docs.txt'); // lang/en/docs.txt
 * $content = $lang->content('about.txt'); // lang/ru/about.txt
 *
 * echo 'Default language: ' . Lang::$defaultLang . "\r\n";
 * echo 'Default dir: ' . Lang::$defaultDir . "\r\n";
 * echo 'Current language: ' . $lang->getLang() . "\r\n";
 * echo 'Current dir: ' . $lang->getDir();
 * ```php
 */
class Lang
{

    /**
     * @var string|null
     */
    static public ?string $defaultDir = null;
    /**
     * @var string|null
     */
    static public ?string $defaultLang = null;

    /**
     * @var string
     */
    protected string $dir;
    /**
     * @var string
     */
    protected string $lang;
    /**
     * @var array
     */
    protected array $data = [];

    /**
     * @param string $dir
     * @param string $lang
     * @param array|null $files
     */
    public function __construct(string $dir, string $lang, ?array $files = null)
    {
        $this->dir = rtrim($dir, '/\\');
        $this->lang = $lang;

        if (!$files)
            return;

        foreach ($files as $file => $key) {
            if (is_int($file)) {
                $file = $key;
                $key = null;
            }

            $this->load($file, $key);
        }
    }

    /**
     * @return string
     */
    public function getLang(): string
    {
        return $this->lang;
    }

    /**
     * @return string
     */
    public function getDir(): string
    {
        return $this->dir;
    }

    /**
     * @param array<string, string|array> $data
     */
    public function loadData(array $data)
    {
        $this->data = array_replace_recursive($this->data, $data);
    }

    /**
     * @param string $file
     * @param string|null $key
     */
    public function loadFile(string $file, ?string $key = null)
    {
        if (!file_exists($file) || true === ($data = include_once($file)))
            return;
        elseif ($key)
            $data = [$key => $data];

        $this->loadData($data);
    }

    /**
     * @param string|array $file_or_data
     * @param string|null $key
     */
    public function load($file_or_data, ?string $key = null)
    {
        if (is_array($file_or_data)) {
            $this->loadData(($key ? [$key => $file_or_data] : $file_or_data));
        } else {
            $this->loadFile("{$this->dir}/{$file_or_data}.php", $key);
        }
    }

    /**
     * @param string|array $path
     * @param mixed $args
     *
     * @return string
     */
    public function get($path, ...$args): string
    {
        if (is_string($path))
            $path = explode('>', $path);
        $key = array_pop($path);

        if (!($value = $path
            ? ($this->params($path)[$key] ?? $key)
            : ($this->data[$key] ?? $key))
        )
            $value = $key;

        return $args ? $value : sprintf($value, ...$args);
    }

    /**
     * @param string|array $path
     *
     * @return array<string, string|array>
     */
    public function params($path): array
    {
        if (is_string($path))
            $path = explode('>', $path);

        $data = $this->data;
        foreach ($path as $key) {
            if (!isset($data[$key]))
                return [];

            $data = $data[$key];
        }

        return $data;
    }

    /**
     * @param string $file
     *
     * @return array<string, string|array>
     */
    public function file(string $file): array
    {
        if (($file = $this->findFile($file)))
            return include $file;
        else
            return [];
    }

    /**
     * @param string $file
     *
     * @return string
     */
    public function content(string $file): string
    {
        if (($file = $this->findFile($file)))
            return file_get_contents($file);
        else
            return '';
    }

    /**
     * @param string $file
     *
     * @return string|null
     */
    public function findFile(string $file): ?string
    {
        if (file_exists("{$this->dir}/{$file}.php"))
            return "{$this->dir}/{$file}.php";
        elseif (static::$defaultDir && static::$defaultDir !== $this->dir
                && file_exists(static::$defaultDir . "/{$file}.php")
        )
            return static::$defaultDir . "/{$file}.php";

        return null;
    }

    /**
     * @param string|array $path
     *
     * @return string
     */
    public function __invoke($path): string
    {
        return $this->get($path);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->lang;
    }

}
