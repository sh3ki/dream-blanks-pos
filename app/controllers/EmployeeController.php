<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Services\AuthService;

class EmployeeController extends Controller
{
    public function index(Request $request): void
    {
        $this->authorizeAnyPermission(['employees.view', 'users.view']);

        $search = trim((string) $request->input('search', ''));
        $page = max(1, (int) $request->input('page', 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        $pdo = Database::connection();

        $sql = 'SELECT e.*, r.role_name
                FROM employees e
                INNER JOIN users u ON u.id = e.user_id
                INNER JOIN roles r ON r.id = u.role_id';

        $countSql = 'SELECT COUNT(*) AS total
                     FROM employees e
                     INNER JOIN users u ON u.id = e.user_id
                     INNER JOIN roles r ON r.id = u.role_id';

        $params = [];
        if ($search !== '') {
            $where = ' WHERE e.first_name LIKE :search OR e.last_name LIKE :search OR e.employee_id LIKE :search OR e.email LIKE :search';
            $sql .= $where;
            $countSql .= $where;
            $params['search'] = '%' . $search . '%';
        }

        $countStmt = $pdo->prepare($countSql);
        $countStmt->execute($params);
        $total = (int) ($countStmt->fetch()['total'] ?? 0);
        $totalPages = max(1, (int) ceil($total / $perPage));
        if ($page > $totalPages) {
            $page = $totalPages;
            $offset = ($page - 1) * $perPage;
        }

        $sql .= ' ORDER BY e.created_at DESC LIMIT :limit OFFSET :offset';
        $stmt = $pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value, \PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        $employees = $stmt->fetchAll();

        $roles = $pdo->query('SELECT id, role_name FROM roles ORDER BY role_name')->fetchAll();

        $this->render('employees.list', [
            'title' => 'Employees',
            'employees' => $employees,
            'roles' => $roles,
            'search' => $search,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'totalPages' => $totalPages,
            'flash' => consume_flash(),
        ]);
    }

    public function store(Request $request): void
    {
        $this->authorizeAnyPermission(['employees.create', 'users.create']);

        $firstName = trim((string) $request->input('first_name'));
        $lastName = trim((string) $request->input('last_name'));
        $email = trim((string) $request->input('email'));
        $username = trim((string) $request->input('username'));
        $password = (string) $request->input('password');
        $roleId = (int) $request->input('role_id');

        $photoPath = null;
        try {
            $photoPath = store_uploaded_image('avatar', 'employees');
        } catch (\Throwable $e) {
            flash('error', $e->getMessage());
            $this->redirect('/employees');
        }

        if ($firstName === '' || $lastName === '' || $email === '' || $username === '' || $password === '' || $roleId < 1) {
            flash('error', 'Please fill all required employee fields.');
            $this->redirect('/employees');
        }

        $employeeId = 'EMP-' . date('Y') . '-' . str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);

        $service = new AuthService();
        $service->createUserWithEmployee(
            [
                'username' => $username,
                'email' => $email,
                'password' => $password,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'role_id' => $roleId,
            ],
            [
                'employee_id' => $employeeId,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'phone' => $request->input('phone'),
                'department' => $request->input('department'),
                'position' => $request->input('position'),
                'hire_date' => $request->input('hire_date') ?: date('Y-m-d'),
                'photo_path' => $photoPath,
            ]
        );

        flash('success', 'Employee created successfully.');
        $this->redirect('/employees');
    }

    public function updateStatus(Request $request, array $params): void
    {
        $this->authorizeAnyPermission(['employees.update', 'users.update']);

        $employeeId = (int) ($params['id'] ?? 0);
        $status = (string) $request->input('status', 'active');
        $isActive = $status === 'active' ? 1 : 0;

        if ($employeeId < 1) {
            flash('error', 'Invalid employee target.');
            $this->redirect('/employees');
        }

        $pdo = Database::connection();
        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare('UPDATE employees SET is_active = :is_active, updated_at = NOW() WHERE id = :id');
            $stmt->execute([
                'is_active' => $isActive,
                'id' => $employeeId,
            ]);

            $userStmt = $pdo->prepare(
                'UPDATE users u
                 INNER JOIN employees e ON e.user_id = u.id
                 SET u.is_active = :is_active, u.updated_at = NOW()
                 WHERE e.id = :employee_id'
            );
            $userStmt->execute([
                'is_active' => $isActive,
                'employee_id' => $employeeId,
            ]);

            $pdo->commit();
            log_activity('employee.status.updated', ['employee_id' => $employeeId, 'is_active' => $isActive]);
            flash('success', 'Employee status updated successfully.');
        } catch (\Throwable $e) {
            $pdo->rollBack();
            flash('error', 'Unable to update employee status: ' . $e->getMessage());
        }

        $this->redirect('/employees');
    }
}
