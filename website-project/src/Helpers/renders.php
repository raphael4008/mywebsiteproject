<?php

namespace App\Helpers;

function render(string $template, array $data = [], string $layout = 'layout'): void
{
    $layoutPath = __DIR__ . '/../../templates/' . $layout . '.php';
    $templatePath = __DIR__ . '/../../templates/' . $template . '.php';

    if (!file_exists($layoutPath) || !file_exists($templatePath)) {
        echo "Error: Template not found.";
        return;
    }

    // Make data available to the template
    extract($data);

    // Capture the content of the main template
    ob_start();
    include $templatePath;
    $content = ob_get_clean();

    // The layout will have access to the original data and the captured content
    $data['content'] = $content;

    // Create a function to replace placeholders
    $render_layout = function ($path, $data) {
        extract($data);
        ob_start();
        include $path;
        $layout_content = ob_get_clean();

        // Replace placeholders
        $layout_content = str_replace('{{ content }}', $data['content'], $layout_content);
        $layout_content = str_replace('{{ basePath }}', $GLOBALS['basePath'] ?? '', $layout_content);
        foreach ($data as $key => $value) {
            if ($key === 'content')
                continue; // Already replaced
            if (!is_array($value) && !is_object($value)) {
                $layout_content = str_replace("{{ " . $key . " }}", $value, $layout_content);
            }
        }
        echo $layout_content;
    };

    $render_layout($layoutPath, $data);
}