<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;

class ExpenseController extends Controller
{
    public function index(Request $request): void
    {
        $this->authorizeAnyPermission(['expenses.view', 'expenses.manage']);

        $pdo = Database::connection();
        $expenses = $pdo->query(
            'SELECT e.*, c.category_name,
                    CONCAT(u.first_name, " ", u.last_name) AS created_by_name
             FROM expenses e
             INNER JOIN expense_categories c ON c.id = e.expense_category_id
             INNER JOIN users u ON u.id = e.created_by
             ORDER BY e.expense_date DESC'
        )->fetchAll();

        $categories = $pdo->query('SELECT id, category_name FROM expense_categories ORDER BY category_name')->fetchAll();

        $this->render('expenses.list', [
            'title' => 'Expenses',
            'expenses' => $expenses,
            'categories' => $categories,
            'flash' => consume_flash(),
        ]);
    }

    public function store(Request $request): void
    {
        $this->authorizeAnyPermission(['expenses.create', 'expenses.manage']);

        $categoryId = (int) $request->input('expense_category_id');
        $description = trim((string) $request->input('description'));
        $amount = (float) $request->input('amount');

        if ($categoryId < 1 || $description === '' || $amount <= 0) {
            flash('error', 'Expense category, description, and amount are required.');
            $this->redirect('/expenses');
        }

        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'INSERT INTO expenses (expense_category_id, description, amount, payment_method, reference_number, created_by, status, expense_date, created_at, updated_at)
             VALUES (:expense_category_id, :description, :amount, :payment_method, :reference_number, :created_by, :status, :expense_date, NOW(), NOW())'
        );
        $stmt->execute([
            'expense_category_id' => $categoryId,
            'description' => $description,
            'amount' => $amount,
            'payment_method' => $request->input('payment_method', 'Cash'),
            'reference_number' => $request->input('reference_number'),
            'created_by' => (int) Auth::id(),
            'status' => 'Pending',
            'expense_date' => $request->input('expense_date') ?: date('Y-m-d'),
        ]);

        flash('success', 'Expense created and marked as Pending.');
        $this->redirect('/expenses');
    }

    public function approve(Request $request, array $params): void
    {
        $this->authorizeAnyPermission(['expenses.approve', 'expenses.manage']);
        $expenseId = (int) ($params['id'] ?? 0);

        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'UPDATE expenses
             SET approver_id = :approver_id, status = :status, approval_date = NOW(), updated_at = NOW()
             WHERE id = :id'
        );
        $stmt->execute([
            'approver_id' => (int) Auth::id(),
            'status' => 'Approved',
            'id' => $expenseId,
        ]);

        flash('success', 'Expense approved.');
        $this->redirect('/expenses');
    }

    public function reject(Request $request, array $params): void
    {
        $this->authorizeAnyPermission(['expenses.approve', 'expenses.manage']);
        $expenseId = (int) ($params['id'] ?? 0);

        if ($expenseId < 1) {
            flash('error', 'Invalid expense target.');
            $this->redirect('/expenses');
        }

        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'UPDATE expenses
             SET approver_id = :approver_id, status = :status, approval_date = NOW(), updated_at = NOW()
             WHERE id = :id'
        );
        $stmt->execute([
            'approver_id' => (int) Auth::id(),
            'status' => 'Rejected',
            'id' => $expenseId,
        ]);

        log_activity('expense.rejected', ['expense_id' => $expenseId]);
        flash('success', 'Expense rejected.');
        $this->redirect('/expenses');
    }

    public function markPaid(Request $request, array $params): void
    {
        $this->authorizeAnyPermission(['expenses.approve', 'expenses.manage']);
        $expenseId = (int) ($params['id'] ?? 0);

        if ($expenseId < 1) {
            flash('error', 'Invalid expense target.');
            $this->redirect('/expenses');
        }

        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'UPDATE expenses
             SET approver_id = :approver_id, status = :status, updated_at = NOW()
             WHERE id = :id'
        );
        $stmt->execute([
            'approver_id' => (int) Auth::id(),
            'status' => 'Paid',
            'id' => $expenseId,
        ]);

        log_activity('expense.paid', ['expense_id' => $expenseId]);
        flash('success', 'Expense marked as paid.');
        $this->redirect('/expenses');
    }
}
