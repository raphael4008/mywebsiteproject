<?php

class View
{
    public static function render($view, $data = [])
    {
        $viewPath = __DIR__ . '/../../templates/' . $view . '.php';
        $layoutPath = __DIR__ . '/../../templates/layout.php';

        extract($data);

        ob_start();
        require_once $viewPath;
        $content = ob_get_clean();

        ob_start();
        require_once $layoutPath;
        $layoutContent = ob_get_clean();

        echo str_replace(['{{ content }}', '{{ title }}'], [$content, $title], $layoutContent);
    }
}
