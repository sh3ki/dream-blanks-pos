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
        $permissions = self::resolvePermissions((int) $user['role_id']);

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
            'permissions' => $permissions,
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

    public static function hasPermission(string $permission): bool
    {
        $permission = trim($permission);
        if ($permission === '') {
            return false;
        }

        if (!self::check()) {
            return false;
        }

        if (self::hasRole(['Admin'])) {
            return true;
        }

        $permissions = self::permissions();
        if (in_array($permission, $permissions, true)) {
            return true;
        }

        return self::legacyRolePermissionFallback($permission);
    }

    public static function hasAnyPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (self::hasPermission((string) $permission)) {
                return true;
            }
        }

        return false;
    }

    public static function hasAllPermissions(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (!self::hasPermission((string) $permission)) {
                return false;
            }
        }

        return true;
    }

    public static function permissions(): array
    {
        $rawPermissions = self::user()['permissions'] ?? [];
        if (!is_array($rawPermissions)) {
            return [];
        }

        return array_values(array_unique(array_filter(array_map('strval', $rawPermissions), static fn (string $item): bool => $item !== '')));
    }

    public static function refreshPermissions(): void
    {
        if (!self::check()) {
            return;
        }

        $roleId = (int) (self::user()['role_id'] ?? 0);
        $_SESSION['user']['permissions'] = self::resolvePermissions($roleId);
    }

    private static function resolvePermissions(int $roleId): array
    {
        if ($roleId < 1) {
            return [];
        }

        try {
            $pdo = Database::connection();
            $stmt = $pdo->prepare(
                'SELECT p.permission_name
                 FROM role_permissions rp
                 INNER JOIN permissions p ON p.id = rp.permission_id
                 WHERE rp.role_id = :role_id
                 ORDER BY p.permission_name ASC'
            );
            $stmt->execute(['role_id' => $roleId]);

            $permissions = [];
            foreach ($stmt->fetchAll() as $row) {
                $permissionName = (string) ($row['permission_name'] ?? '');
                if ($permissionName !== '') {
                    $permissions[] = $permissionName;
                }
            }

            return array_values(array_unique($permissions));
        } catch (\Throwable $e) {
            return [];
        }
    }

    private static function legacyRolePermissionFallback(string $permission): bool
    {
        $role = (string) (self::user()['role_name'] ?? '');
        if ($role === '') {
            return false;
        }

        $permissionRoles = [
            'dashboard.view' => ['Admin', 'Manager', 'Cashier', 'Store Staff', 'Accountant'],
            'users.view' => ['Admin', 'Manager'],
            'users.create' => ['Admin', 'Manager'],
            'users.update' => ['Admin', 'Manager'],
            'employees.view' => ['Admin', 'Manager'],
            'employees.create' => ['Admin', 'Manager'],
            'employees.update' => ['Admin', 'Manager'],
            'employees.delete' => ['Admin'],
            'products.view' => ['Admin', 'Manager', 'Store Staff', 'Cashier'],
            'products.create' => ['Admin', 'Manager', 'Store Staff'],
            'products.update' => ['Admin', 'Manager', 'Store Staff'],
            'products.delete' => ['Admin', 'Manager'],
            'products.manage' => ['Admin', 'Manager', 'Store Staff'],
            'categories.view' => ['Admin', 'Manager', 'Store Staff'],
            'categories.create' => ['Admin', 'Manager'],
            'categories.update' => ['Admin', 'Manager'],
            'categories.delete' => ['Admin', 'Manager'],
            'inventory.view' => ['Admin', 'Manager', 'Store Staff', 'Cashier'],
            'inventory.adjust' => ['Admin', 'Manager', 'Store Staff'],
            'sales.process' => ['Admin', 'Manager', 'Cashier'],
            'sales.view' => ['Admin', 'Manager', 'Cashier', 'Accountant'],
            'sales.refund' => ['Admin', 'Manager'],
            'sales.void' => ['Admin', 'Manager'],
            'expenses.view' => ['Admin', 'Manager', 'Accountant'],
            'expenses.create' => ['Admin', 'Manager', 'Accountant'],
            'expenses.approve' => ['Admin', 'Manager'],
            'expenses.delete' => ['Admin', 'Manager'],
            'expenses.manage' => ['Admin', 'Manager', 'Accountant'],
            'reports.view' => ['Admin', 'Manager', 'Store Staff', 'Accountant'],
            'reports.export' => ['Admin', 'Manager', 'Accountant'],
            'transactions.view' => ['Admin', 'Manager', 'Accountant'],
            'notifications.view' => ['Admin', 'Manager', 'Cashier', 'Store Staff', 'Accountant'],
            'roles.manage' => ['Admin'],
            'permissions.manage' => ['Admin'],
        ];

        $allowedRoles = $permissionRoles[$permission] ?? [];
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
