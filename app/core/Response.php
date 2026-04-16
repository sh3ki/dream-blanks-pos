<?php

namespace App\Core;

class Response
{
    public static function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_SLASHES);
    }

    public static function redirect(string $path): void
    {
        if (!preg_match('#^https?://#i', $path) && str_starts_with($path, '/')) {
            $path = url($path);
        }

        header('Location: ' . $path);
        exit;
    }
}
