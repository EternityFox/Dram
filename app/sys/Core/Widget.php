<?php declare(strict_types=1);

namespace Core;

abstract class Widget
{

    /**
     * @var Viewer
     */
    static public Viewer $viewer;

    /**
     * @return string
     */
    public function getName(): string
    {
        return substr(strrchr(get_called_class(), '\\'), 1);
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        $data = ['widget' => $this];
        foreach ($this as $key => $val) {
            $data[$key] = $val;
        }

        return $data;
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function config(string $key)
    {
        return App::config("widget>{$this->getName()}>{$key}");
    }

    /**
     * @param string|null $template
     * @param bool $toString
     *
     * @return string|null
     */
    public function render(?string $template = null, bool $toString = false)
    {
        if ($toString)
            ob_start();
        static::$viewer->render(
            'widget/' . ($template ?? $this->getName()),
            $this->getData(),
            false
        );

        return $toString ? ob_get_clean() : null;
    }

}

Widget::$viewer = \App::get('viewer');
