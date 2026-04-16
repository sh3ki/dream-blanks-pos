<?php

use App\Core\Auth;
use App\Core\Csrf;

function base_path(): string
{
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $dir = rtrim(dirname($scriptName), '/');

    if ($dir === '' || $dir === '.' || $dir === '/') {
        return '';
    }

    if (str_ends_with($dir, '/public')) {
        $dir = substr($dir, 0, -7);
    }

    return rtrim($dir, '/');
}

function url(string $path = '/'): string
{
    $base = base_path();
    if ($path === '' || $path === '/') {
        return $base === '' ? '/' : $base . '/';
    }

    $normalized = '/' . ltrim($path, '/');
    return ($base === '' ? '' : $base) . $normalized;
}

function asset_url(string $path): string
{
    return url('/public/assets/' . ltrim($path, '/'));
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . e(Csrf::token()) . '">';
}

function old(string $key, mixed $default = ''): string
{
    return e($_SESSION['_old'][$key] ?? $default);
}

function set_old(array $values): void
{
    $_SESSION['_old'] = $values;
}

function clear_old(): void
{
    unset($_SESSION['_old']);
}

function flash(string $type, string $message): void
{
    $_SESSION['_flash'][] = ['type' => $type, 'message' => $message];
}

function consume_flash(): array
{
    $flash = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);
    return $flash;
}

function current_user(): ?array
{
    return Auth::user();
}
