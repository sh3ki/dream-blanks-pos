<?php

namespace App\Services;

use App\Core\Database;

class DashboardService
{
    public function summary(): array
    {
        $pdo = Database::connection();

        $todaySales = (float) $pdo->query("SELECT COALESCE(SUM(total), 0) AS value FROM sales WHERE DATE(created_at) = CURDATE() AND status = 'Completed'")->fetch()['value'];
        $todayTransactions = (int) $pdo->query("SELECT COUNT(*) AS value FROM sales WHERE DATE(created_at) = CURDATE() AND status = 'Completed'")->fetch()['value'];
        $todayExpenses = (float) $pdo->query("SELECT COALESCE(SUM(amount), 0) AS value FROM expenses WHERE DATE(expense_date) = CURDATE() AND status IN ('Approved', 'Paid')")->fetch()['value'];
        $lowStock = (int) $pdo->query('SELECT COUNT(*) AS value FROM inventory WHERE quantity_on_hand <= reorder_level')->fetch()['value'];

        $avgTicket = $todayTransactions > 0 ? $todaySales / $todayTransactions : 0;

        return [
            'today_sales' => $todaySales,
            'today_transactions' => $todayTransactions,
            'average_ticket' => $avgTicket,
            'today_expenses' => $todayExpenses,
            'low_stock_items' => $lowStock,
        ];
    }

    public function recentSales(int $limit = 10): array
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'SELECT s.id, s.transaction_number, s.total, s.payment_method, s.created_at,
                    CONCAT(u.first_name, " ", u.last_name) AS cashier_name
             FROM sales s
             INNER JOIN users u ON u.id = s.cashier_id
             ORDER BY s.created_at DESC
             LIMIT :limit'
        );
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
