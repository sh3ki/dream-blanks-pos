<?php

namespace App\Core;

use Throwable;

class App
{
    public static function boot(): void
    {
        require APP_ROOT . '/config/constants.php';
        Env::load(APP_ROOT . '/.env');

        $app = require APP_ROOT . '/config/app.php';
        date_default_timezone_set($app['timezone']);

        session_name($app['session_name']);
        session_start([
            'cookie_httponly' => true,
            'cookie_samesite' => 'Lax',
            'use_strict_mode' => true,
        ]);
    }

    public static function run(): void
    {
        $router = new Router();
        require APP_ROOT . '/routes/web.php';
        require APP_ROOT . '/routes/api.php';

        try {
            $router->dispatch(new Request());
        } catch (Throwable $e) {
            self::handleException($e);
        }
    }

    private static function handleException(Throwable $e): void
    {
        $app = require APP_ROOT . '/config/app.php';
        $message = '[' . date('Y-m-d H:i:s') . '] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine() . PHP_EOL;
        error_log($message, 3, LOG_PATH);

        http_response_code(500);
        if ($app['debug']) {
            echo nl2br(htmlspecialchars($e->getMessage() . "\n" . $e->getTraceAsString(), ENT_QUOTES, 'UTF-8'));
            return;
        }

        echo 'Internal Server Error';
    }
}
