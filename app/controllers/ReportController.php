<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;

class ReportController extends Controller
{
    public function sales(Request $request): void
    {
        $this->authorize(['Admin', 'Manager', 'Accountant']);

        $pdo = Database::connection();
        $rows = $pdo->query(
            'SELECT DATE(created_at) AS report_date,
                    COUNT(*) AS transaction_count,
                    COALESCE(SUM(total), 0) AS gross_sales
             FROM sales
             GROUP BY DATE(created_at)
             ORDER BY report_date DESC
             LIMIT 60'
        )->fetchAll();

        $this->render('reports.sales', [
            'title' => 'Sales Report',
            'rows' => $rows,
            'flash' => consume_flash(),
        ]);
    }

    public function inventory(Request $request): void
    {
        $this->authorize(['Admin', 'Manager', 'Store Staff', 'Accountant']);

        $pdo = Database::connection();
        $rows = $pdo->query(
            'SELECT p.sku, p.product_name, i.quantity_on_hand, i.reorder_level,
                    (i.quantity_on_hand * p.cost_price) AS stock_value
             FROM inventory i
             INNER JOIN products p ON p.id = i.product_id
             ORDER BY p.product_name'
        )->fetchAll();

        $this->render('reports.inventory', [
            'title' => 'Inventory Report',
            'rows' => $rows,
            'flash' => consume_flash(),
        ]);
    }

    public function expenses(Request $request): void
    {
        $this->authorize(['Admin', 'Manager', 'Accountant']);

        $pdo = Database::connection();
        $rows = $pdo->query(
            'SELECT DATE(expense_date) AS report_date, COUNT(*) AS entry_count,
                    COALESCE(SUM(amount), 0) AS total_expense
             FROM expenses
             GROUP BY DATE(expense_date)
             ORDER BY report_date DESC
             LIMIT 60'
        )->fetchAll();

        $this->render('reports.expenses', [
            'title' => 'Expense Report',
            'rows' => $rows,
            'flash' => consume_flash(),
        ]);
    }
}
