<?php

namespace App\Core;

class Request
{
    public function method(): string
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        if ($method === 'POST' && isset($_POST['_method'])) {
            $override = strtoupper((string) $_POST['_method']);
            if (in_array($override, ['PUT', 'PATCH', 'DELETE'], true)) {
                return $override;
            }
        }

        return $method;
    }

    public function path(): string
    {
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');

        if ($scriptDir !== '' && $scriptDir !== '/' && str_starts_with($uri, $scriptDir)) {
            $uri = substr($uri, strlen($scriptDir));
        } elseif ($scriptDir !== '' && str_ends_with($scriptDir, '/public')) {
            $baseDir = substr($scriptDir, 0, -7);
            if ($baseDir !== '' && $baseDir !== '/' && str_starts_with($uri, $baseDir)) {
                $uri = substr($uri, strlen($baseDir));
            }
        }

        $uri = '/' . ltrim($uri, '/');
        if ($uri === '') {
            return '/';
        }

        $normalized = rtrim($uri, '/');
        return $normalized === '' ? '/' : $normalized;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    public function all(): array
    {
        return array_merge($_GET, $_POST);
    }

    public function only(array $keys): array
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->input($key);
        }

        return $result;
    }
}
