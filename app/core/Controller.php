<?php

namespace App\Core;

class Controller
{
    protected function authorize(array $roles): void
    {
        if (!Auth::check() || !Auth::hasRole($roles)) {
            $this->denyForbidden();
        }
    }

    protected function authorizePermission(string $permission): void
    {
        if (!Auth::check() || !Auth::hasPermission($permission)) {
            $this->denyForbidden();
        }
    }

    protected function authorizeAnyPermission(array $permissions): void
    {
        if (!Auth::check() || !Auth::hasAnyPermission($permissions)) {
            $this->denyForbidden();
        }
    }

    protected function authorizeAllPermissions(array $permissions): void
    {
        if (!Auth::check() || !Auth::hasAllPermissions($permissions)) {
            $this->denyForbidden();
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

    private function denyForbidden(): void
    {
        http_response_code(403);
        exit('Forbidden');
    }
}
