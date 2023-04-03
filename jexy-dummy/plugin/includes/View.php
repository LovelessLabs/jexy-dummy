<?php

namespace Jexy\Dummy;

class View
{
    protected $template;
    protected $data = [];

    public function __construct($template)
    {
        $this->template = $template;
    }

    public function __get($key)
    {
        return $this->data[$key];
    }

    public function __set($key, $value)
    {
        $this->data[$key] = $value;
    }

    public function splitTemplatePath($template)
    {
        $parts = explode('/', $template);

        $templateType = array_shift($parts);
        $template = implode('/', $parts);

        return [$templateType, $template];
    }

    public function render()
    {
        extract($this->data);
        $parts = $this->splitTemplatePath($this->template);
        include dirname(__DIR__) . "/{$parts[0]}/views/{$parts[1]}.php";
    }

    public function asString()
    {
        ob_start();
        $this->render();
        return ob_get_clean();
    }
}
