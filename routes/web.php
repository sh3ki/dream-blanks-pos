<?php

use App\Controllers\AuthController;
use App\Controllers\CategoryController;
use App\Controllers\DashboardController;
use App\Controllers\EmployeeController;
use App\Controllers\ExpenseController;
use App\Controllers\InventoryController;
use App\Controllers\NotificationController;
use App\Controllers\ProductController;
use App\Controllers\ReportController;
use App\Controllers\SalesController;
use App\Controllers\TransactionController;
use App\Core\Auth;
use App\Core\Response;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Middleware\GuestMiddleware;

$router->get('/', function () {
    if (Auth::check()) {
        Response::redirect('/dashboard');
        return;
    }

    Response::redirect('/login');
});

$router->get('/login', [AuthController::class, 'showLogin'], [GuestMiddleware::class]);
$router->post('/login', [AuthController::class, 'login'], [GuestMiddleware::class, CsrfMiddleware::class]);
$router->post('/logout', [AuthController::class, 'logout'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/profile', [AuthController::class, 'profile'], [AuthMiddleware::class]);
$router->post('/profile', [AuthController::class, 'updateProfile'], [AuthMiddleware::class, CsrfMiddleware::class]);

$router->get('/dashboard', [DashboardController::class, 'index'], [AuthMiddleware::class]);

$router->get('/employees', [EmployeeController::class, 'index'], [AuthMiddleware::class]);
$router->post('/employees', [EmployeeController::class, 'store'], [AuthMiddleware::class, CsrfMiddleware::class]);

$router->get('/categories', [CategoryController::class, 'index'], [AuthMiddleware::class]);
$router->post('/categories', [CategoryController::class, 'store'], [AuthMiddleware::class, CsrfMiddleware::class]);

$router->get('/products', [ProductController::class, 'index'], [AuthMiddleware::class]);
$router->post('/products', [ProductController::class, 'store'], [AuthMiddleware::class, CsrfMiddleware::class]);

$router->get('/inventory', [InventoryController::class, 'index'], [AuthMiddleware::class]);
$router->post('/inventory/adjust', [InventoryController::class, 'adjust'], [AuthMiddleware::class, CsrfMiddleware::class]);

$router->get('/pos', [SalesController::class, 'pos'], [AuthMiddleware::class]);
$router->post('/pos/checkout', [SalesController::class, 'store'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/sales', [SalesController::class, 'index'], [AuthMiddleware::class]);

$router->get('/expenses', [ExpenseController::class, 'index'], [AuthMiddleware::class]);
$router->post('/expenses', [ExpenseController::class, 'store'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/expenses/{id}/approve', [ExpenseController::class, 'approve'], [AuthMiddleware::class, CsrfMiddleware::class]);

$router->get('/notifications', [NotificationController::class, 'index'], [AuthMiddleware::class]);
$router->post('/notifications/{id}/read', [NotificationController::class, 'markRead'], [AuthMiddleware::class, CsrfMiddleware::class]);

$router->get('/transactions', [TransactionController::class, 'index'], [AuthMiddleware::class]);

$router->get('/reports/sales', [ReportController::class, 'sales'], [AuthMiddleware::class]);
$router->get('/reports/inventory', [ReportController::class, 'inventory'], [AuthMiddleware::class]);
$router->get('/reports/expenses', [ReportController::class, 'expenses'], [AuthMiddleware::class]);
