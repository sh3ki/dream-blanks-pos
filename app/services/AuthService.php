<?php

namespace App\Services;

use App\Core\Database;
use PDO;

class AuthService
{
    public function authenticate(string $identity, string $password): array
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare(
            'SELECT u.*, r.role_name
             FROM users u
             INNER JOIN roles r ON r.id = u.role_id
             WHERE (u.email = :email_identity OR u.username = :username_identity)
             LIMIT 1'
        );
        $stmt->execute([
            'email_identity' => $identity,
            'username_identity' => $identity,
        ]);
        $user = $stmt->fetch();

        if (!$user) {
            return ['ok' => false, 'message' => 'Invalid credentials.'];
        }

        if (!(bool) $user['is_active']) {
            return ['ok' => false, 'message' => 'Account is inactive.'];
        }

        if (!empty($user['locked_until']) && strtotime($user['locked_until']) > time()) {
            return ['ok' => false, 'message' => 'Account is temporarily locked.'];
        }

        if (!password_verify($password, $user['password_hash'])) {
            $failedAttempts = ((int) $user['failed_attempts']) + 1;
            $lockedUntil = null;
            if ($failedAttempts >= 3) {
                $lockedUntil = date('Y-m-d H:i:s', strtotime('+30 minutes'));
                $failedAttempts = 0;
            }

            $update = $pdo->prepare('UPDATE users SET failed_attempts = :failed_attempts, locked_until = :locked_until WHERE id = :id');
            $update->execute([
                'failed_attempts' => $failedAttempts,
                'locked_until' => $lockedUntil,
                'id' => $user['id'],
            ]);

            return ['ok' => false, 'message' => 'Invalid credentials.'];
        }

        $update = $pdo->prepare('UPDATE users SET failed_attempts = 0, locked_until = NULL, last_login = NOW() WHERE id = :id');
        $update->execute(['id' => $user['id']]);

        return ['ok' => true, 'user' => $user];
    }

    public function createUserWithEmployee(array $userData, array $employeeData): int
    {
        $pdo = Database::connection();
        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare(
                'INSERT INTO users (username, email, password_hash, first_name, last_name, role_id, is_active, created_at, updated_at)
                 VALUES (:username, :email, :password_hash, :first_name, :last_name, :role_id, 1, NOW(), NOW())'
            );
            $stmt->execute([
                'username' => $userData['username'],
                'email' => $userData['email'],
                'password_hash' => password_hash($userData['password'], PASSWORD_BCRYPT),
                'first_name' => $userData['first_name'],
                'last_name' => $userData['last_name'],
                'role_id' => $userData['role_id'],
            ]);

            $userId = (int) $pdo->lastInsertId();

            $employeeStmt = $pdo->prepare(
                'INSERT INTO employees (employee_id, first_name, last_name, email, phone, department, position, hire_date, photo_path, is_active, user_id, created_at, updated_at)
                 VALUES (:employee_id, :first_name, :last_name, :email, :phone, :department, :position, :hire_date, :photo_path, 1, :user_id, NOW(), NOW())'
            );
            $employeeStmt->execute([
                'employee_id' => $employeeData['employee_id'],
                'first_name' => $employeeData['first_name'],
                'last_name' => $employeeData['last_name'],
                'email' => $employeeData['email'],
                'phone' => $employeeData['phone'] ?? null,
                'department' => $employeeData['department'] ?? null,
                'position' => $employeeData['position'] ?? null,
                'hire_date' => $employeeData['hire_date'] ?? date('Y-m-d'),
                'photo_path' => $employeeData['photo_path'] ?? null,
                'user_id' => $userId,
            ]);

            $pdo->commit();
            return $userId;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
