<?php

namespace App\Core;

class Controller
{
    protected function authorize(array $roles): void
    {
        if (!Auth::check() || !Auth::hasRole($roles)) {
            http_response_code(403);
            exit('Forbidden');
        }
    }

    protected function render(string $view, array $data = [], string $layout = 'app'): void
    {
        View::render($view, $data, $layout);
    }

    protected function json(array $data, int $status = 200): void
    {
        Response::json($data, $status);
    }

    protected function redirect(string $path): void
    {
        Response::redirect($path);
    }
}
