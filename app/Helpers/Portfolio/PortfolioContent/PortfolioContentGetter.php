<?php namespace MotionArray\Helpers\Portfolio\PortfolioContent;

abstract class PortfolioContentGetter
{
    public function __construct(Array $settings)
    {
        $this->settings = $settings;

        $this->jsonSettings = json_decode(json_encode($settings));
    }

    public function __get($name)
    {
        if (isset($this->jsonSettings->{$name})) {
            return $this->jsonSettings->{$name};
        }

        if (isset($this->settings[$name])) {
            return $this->settings[$name];
        }
    }

    public function get($path, $default = null, $prefix = null)
    {
        $content = $this->getContent($path, null, $prefix);

        if (!is_null($content)) {
            return $content;
        }

        $style = $this->getStyle($path, null, $prefix);

        if (!is_null($style)) {
            return $style;
        }

        return $default;
    }

    public function getContent($path, $default = null, $prefix = null)
    {
        $prefix = $prefix ? $prefix . '.' : '';

        $content = array_get($this->settings, ($prefix . 'content.' . $path));

        if (!is_null($content)) {
            return $content;
        }

        return $default;
    }

    public function getStyle($path, $default = null, $prefix = null)
    {
        $prefix = $prefix ? $prefix . '.' : '';

        $content = array_get($this->settings, ($prefix . 'styles.' . $path));

        if (!is_null($content)) {
            return $content;
        }

        return $default;
    }
}