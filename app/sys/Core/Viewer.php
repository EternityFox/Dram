<?php declare(strict_types=1);

namespace Core;

/**
 * ```php
 * $viewer = new Viewer(__DIR__ . '/view', 'layout', ['title' => 'MySite']);
 *
 * $viewer->setDir($viewer->getDir() . '/shop');
 * $viewer->setLayout('shop_layout');
 *
 * $viewer->set('title', 'ShopName');
 * $viewer->set(['name' => 'ProductName', 'price' => '1.000']);
 *
 * $viewer->render(
 *     'product/view',
 *     [
 *         'title' => $viewer->get('title') . ' | ProductName',
 *         'name' => 'ProductName',
 *         'price' => '1.000'
 *     ]
 * );
 * $viewer->render('product/edit', [], 'layout');
 * $viewer->render('site/menu', [], false);
 * $viewer->render([]);
 *
 * $viewer->renderFile(__DIR__ . '/view/single_file.php', [...]);
 *
 * echo "Dir: {$viewer->getDir()}\r\n"
 *      . "Layout: {$viewer->getLayout()}\r\n"
 *      . "Title: {$viewer->get('title')}\r\n"
 *      . "All data: " . print_r($viewer->all(), true);
 * ```php
 */
class Viewer
{

    /**
     * @var string
     */
    protected string $__dir;
    /**
     * @var string
     */
    protected string $__layout = 'layout';
    /**
     * @var array
     */
    protected array $__data = [];
    /**
     * @var bool
     */
    protected bool $__showLayout = true;

    /**
     * @param string $dir
     * @param string|null $layout
     * @param array|null $data
     */
    public function __construct(
        string $dir, ?string $layout = null, ?array $data = null
    )
    {
        $this->setDir($dir);
        if ($layout)
            $this->setLayout($layout);
        if ($data)
            $this->set($data);
    }

    /**
     * @param string $name
     *
     * @return mixed|null
     */
    public function __get(string $name)
    {
        return $this->get($name);
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function __set(string $name, $value)
    {
        $this->set($name, $value);
    }

    /**
     * @return string
     */
    public function getDir(): string
    {
        return $this->__dir;
    }

    /**
     * @param string $dir
     */
    public function setDir(string $dir)
    {
        $this->__dir = rtrim($dir, '/\\');
    }

    /**
     * @return string
     */
    public function getLayout(): string
    {
        return $this->__layout;
    }

    /**
     * @param string $layout
     */
    public function setLayout(string $layout)
    {
        $this->__layout = $layout;
    }

    /**
     * @return array
     */
    public function all(): array
    {
        return $this->__data;
    }

    /**
     * @param string $key
     *
     * @return mixed|null
     */
    public function get(string $key)
    {
        return $this->__data[$key] ?? null;
    }

    /**
     * @param string|array $key
     * @param mixed|null $value
     */
    public function set($key, $value = null)
    {
        if (is_array($key))
            $this->__data = $key + $this->__data;
        else
            $this->__data[$key] = $value;
    }

    /**
     * @param string|array|null $template_or_data
     * @param array|null $data
     * @param bool|string|null $layout
     */
    public function render(
        $template_or_data = null,
        ?array $data = null,
        $layout = null
    )
    {
        if (is_array($template_or_data)) {
            $data = $template_or_data;
            $template = $this->__layout;
            $this->__showLayout = false;
        } elseif (false === $layout || (is_null($layout) && !$this->__showLayout)) {
            $template = $template_or_data;
        } else {
            $template = (true === $layout || is_null($layout))
                ? $this->__layout : $layout;
            $data['template'] = $template_or_data;
            $data['template_file'] = $this->getDir()
                                     . "/{$template_or_data}.php";
            $this->__showLayout = false;
        }

        $this->renderFile("{$this->__dir}/{$template}.php", $data);
    }

    /**
     * @param string $file
     * @param array|null $__data
     */
    public function renderFile(
        string $file, ?array $__data = null
    )
    {
        $__data ??= [];
        $__data += $this->__data;

        extract($__data, EXTR_REFS);
        include $file;
    }

}
