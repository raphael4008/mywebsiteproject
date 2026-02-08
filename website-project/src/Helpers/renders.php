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

    // Make data available to the template and layout
    extract($data);

    // Capture the content of the main template
    ob_start();
    include $templatePath;
    $content = ob_get_clean();

    // Capture the content of the layout
    ob_start();
    include $layoutPath;
    $final_output = ob_get_clean();

    // Replace the content placeholder first
    $final_output = str_replace('{{ content }}', $content, $final_output);

    // Replace the base path and other data placeholders
    $final_output = str_replace('{{ basePath }}', $GLOBALS['basePath'] ?? '', $final_output);
    foreach ($data as $key => $value) {
        if (!is_array($value) && !is_object($value)) {
            $final_output = str_replace("{{ " . $key . " }}", $value, $final_output);
        }
    }

    echo $final_output;
}