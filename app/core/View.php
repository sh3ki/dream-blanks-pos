<?php

namespace App\Core;

class View
{
    public static function render(string $view, array $data = [], string $layout = 'app'): void
    {
        $viewFile = VIEW_PATH . '/' . str_replace('.', '/', $view) . '.php';
        $layoutFile = VIEW_PATH . '/layouts/' . $layout . '.php';

        if (!is_file($viewFile)) {
            throw new \RuntimeException('View not found: ' . $view);
        }

        extract($data);
        $contentView = $viewFile;

        if (is_file($layoutFile)) {
            require $layoutFile;
            return;
        }

        require $viewFile;
    }
}
