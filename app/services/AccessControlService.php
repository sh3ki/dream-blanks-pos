<?php

namespace App\Services;

use App\Core\Auth;
use App\Core\Database;

class AccessControlService
{
    public function roles(): array
    {
        $pdo = Database::connection();
        return $pdo->query('SELECT id, role_name, description, created_at FROM roles ORDER BY role_name ASC')->fetchAll();
    }

    public function permissions(): array
    {
        $pdo = Database::connection();
        return $pdo->query('SELECT id, permission_name, module, description, created_at FROM permissions ORDER BY module ASC, permission_name ASC')->fetchAll();
    }

    public function rolePermissionMap(): array
    {
        $pdo = Database::connection();
        $rows = $pdo->query('SELECT role_id, permission_id FROM role_permissions')->fetchAll();

        $map = [];
        foreach ($rows as $row) {
            $roleId = (int) ($row['role_id'] ?? 0);
            $permissionId = (int) ($row['permission_id'] ?? 0);
            if ($roleId > 0 && $permissionId > 0) {
                $map[$roleId][] = $permissionId;
            }
        }

        foreach ($map as $roleId => $permissionIds) {
            $map[$roleId] = array_values(array_unique($permissionIds));
        }

        return $map;
    }

    public function users(): array
    {
        $pdo = Database::connection();
        $stmt = $pdo->query(
            'SELECT u.id, u.username, u.email, u.first_name, u.last_name, u.role_id, r.role_name, u.is_active
             FROM users u
             INNER JOIN roles r ON r.id = u.role_id
             ORDER BY u.first_name ASC, u.last_name ASC'
        );

        return $stmt->fetchAll();
    }

    public function createRole(string $roleName, ?string $description = null): int
    {
        $roleName = trim($roleName);
        if ($roleName === '') {
            throw new \InvalidArgumentException('Role name is required.');
        }

        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'INSERT INTO roles (role_name, description, created_at)
             VALUES (:role_name, :description, NOW())'
        );
        $stmt->execute([
            'role_name' => $roleName,
            'description' => $description !== null ? trim($description) : null,
        ]);

        return (int) $pdo->lastInsertId();
    }

    public function createPermission(string $permissionName, ?string $module = null, ?string $description = null): int
    {
        $permissionName = trim($permissionName);
        if ($permissionName === '') {
            throw new \InvalidArgumentException('Permission name is required.');
        }

        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'INSERT INTO permissions (permission_name, module, description, created_at)
             VALUES (:permission_name, :module, :description, NOW())'
        );
        $stmt->execute([
            'permission_name' => $permissionName,
            'module' => $module !== null ? trim($module) : null,
            'description' => $description !== null ? trim($description) : null,
        ]);

        return (int) $pdo->lastInsertId();
    }

    public function syncRolePermissions(int $roleId, array $permissionIds): void
    {
        if ($roleId < 1) {
            throw new \InvalidArgumentException('Invalid role.');
        }

        $permissionIds = array_values(array_unique(array_map(static fn ($value): int => (int) $value, $permissionIds)));
        $permissionIds = array_values(array_filter($permissionIds, static fn (int $value): bool => $value > 0));

        $pdo = Database::connection();
        $pdo->beginTransaction();

        try {
            $delete = $pdo->prepare('DELETE FROM role_permissions WHERE role_id = :role_id');
            $delete->execute(['role_id' => $roleId]);

            if (count($permissionIds) > 0) {
                $insert = $pdo->prepare(
                    'INSERT INTO role_permissions (role_id, permission_id, created_at)
                     VALUES (:role_id, :permission_id, NOW())'
                );

                foreach ($permissionIds as $permissionId) {
                    $insert->execute([
                        'role_id' => $roleId,
                        'permission_id' => $permissionId,
                    ]);
                }
            }

            $pdo->commit();

            if ((int) (Auth::user()['role_id'] ?? 0) === $roleId) {
                Auth::refreshPermissions();
            }
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public function assignUserRole(int $userId, int $roleId): void
    {
        if ($userId < 1 || $roleId < 1) {
            throw new \InvalidArgumentException('Invalid role assignment payload.');
        }

        $pdo = Database::connection();
        $stmt = $pdo->prepare('UPDATE users SET role_id = :role_id, updated_at = NOW() WHERE id = :id');
        $stmt->execute([
            'role_id' => $roleId,
            'id' => $userId,
        ]);

        if ((int) Auth::id() === $userId) {
            $roleStmt = $pdo->prepare('SELECT role_name FROM roles WHERE id = :id LIMIT 1');
            $roleStmt->execute(['id' => $roleId]);
            $roleRow = $roleStmt->fetch();
            $_SESSION['user']['role_id'] = $roleId;
            $_SESSION['user']['role_name'] = (string) ($roleRow['role_name'] ?? '');
            Auth::refreshPermissions();
        }
    }
}
