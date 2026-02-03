<?php

namespace App\Helpers;

function render(string $template, array $data = []): string
{
    $layoutPath = __DIR__ . '/../../templates/layout.php';
    $templatePath = __DIR__ . '/../../templates/' . $template . '.php';

    if (!file_exists($layoutPath) || !file_exists($templatePath)) {
        return "Error: Template not found.";
    }

    $content = file_get_contents($templatePath);
    $layout = file_get_contents($layoutPath);

    $output = str_replace('{{ content }}', $content, $layout);
    $output = str_replace('{{ basePath }}', $GLOBALS['basePath'] ?? '', $output);

    foreach ($data as $key => $value) {
        $output = str_replace("{{ " . $key . " }}", $value, $output);
    }

    return $output;
}
