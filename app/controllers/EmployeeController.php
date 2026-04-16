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
        $this->authorize(['Admin', 'Manager']);

        $search = trim((string) $request->input('search', ''));
        $pdo = Database::connection();

        $sql = 'SELECT e.*, r.role_name
                FROM employees e
                INNER JOIN users u ON u.id = e.user_id
                INNER JOIN roles r ON r.id = u.role_id';

        $params = [];
        if ($search !== '') {
            $sql .= ' WHERE e.first_name LIKE :search OR e.last_name LIKE :search OR e.employee_id LIKE :search OR e.email LIKE :search';
            $params['search'] = '%' . $search . '%';
        }

        $sql .= ' ORDER BY e.created_at DESC';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $employees = $stmt->fetchAll();

        $roles = $pdo->query('SELECT id, role_name FROM roles ORDER BY role_name')->fetchAll();

        $this->render('employees.list', [
            'title' => 'Employees',
            'employees' => $employees,
            'roles' => $roles,
            'search' => $search,
            'flash' => consume_flash(),
        ]);
    }

    public function store(Request $request): void
    {
        $this->authorize(['Admin', 'Manager']);

        $firstName = trim((string) $request->input('first_name'));
        $lastName = trim((string) $request->input('last_name'));
        $email = trim((string) $request->input('email'));
        $username = trim((string) $request->input('username'));
        $password = (string) $request->input('password');
        $roleId = (int) $request->input('role_id');

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
            ]
        );

        flash('success', 'Employee created successfully.');
        $this->redirect('/employees');
    }
}
