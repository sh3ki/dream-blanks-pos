<?php

namespace App\Core;

class Auth
{
    public static function check(): bool
    {
        self::enforceTimeout();
        return isset($_SESSION['user']);
    }

    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public static function id(): ?int
    {
        return self::user()['id'] ?? null;
    }

    public static function login(array $user): void
    {
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id' => (int) $user['id'],
            'username' => $user['username'],
            'email' => $user['email'] ?? '',
            'first_name' => $user['first_name'] ?? '',
            'last_name' => $user['last_name'] ?? '',
            'name' => trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')),
            'role_id' => (int) $user['role_id'],
            'role_name' => $user['role_name'] ?? 'Staff',
        ];
        $_SESSION['last_activity_at'] = time();
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 3600, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
    }

    public static function hasRole(array $allowedRoles): bool
    {
        $role = self::user()['role_name'] ?? '';
        return in_array($role, $allowedRoles, true);
    }

    private static function enforceTimeout(): void
    {
        if (!isset($_SESSION['last_activity_at'])) {
            return;
        }

        $appConfig = require APP_ROOT . '/config/app.php';
        $timeoutSeconds = ((int) $appConfig['session_timeout']) * 60;
        if (time() - (int) $_SESSION['last_activity_at'] > $timeoutSeconds) {
            self::logout();
            return;
        }

        $_SESSION['last_activity_at'] = time();
    }
}
