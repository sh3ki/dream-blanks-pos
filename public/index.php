<?php

declare(strict_types=1);

use App\Core\App;

define('APP_ROOT', dirname(__DIR__));

require APP_ROOT . '/config/constants.php';
require APP_ROOT . '/app/helpers/functions.php';

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $relativePath = str_replace('\\', '/', $relativeClass) . '.php';
    $directFile = APP_ROOT . '/app/' . $relativePath;
    if (is_file($directFile)) {
        require $directFile;
        return;
    }

    $segments = explode('/', $relativePath);
    if (isset($segments[0])) {
        $segments[0] = strtolower($segments[0]);
    }

    $fallbackFile = APP_ROOT . '/app/' . implode('/', $segments);
    if (is_file($fallbackFile)) {
        require $fallbackFile;
    }
});

App::boot();
App::run();
