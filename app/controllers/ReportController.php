<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;

class ReportController extends Controller
{
    public function sales(Request $request): void
    {
        $this->authorizePermission('reports.view');

        $fromDate = $this->normalizeDate((string) $request->input('from_date', ''));
        $toDate = $this->normalizeDate((string) $request->input('to_date', ''));
        $rows = $this->fetchSalesRows($fromDate, $toDate);

        $this->render('reports.sales', [
            'title' => 'Sales Report',
            'rows' => $rows,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'flash' => consume_flash(),
        ]);
    }

    public function inventory(Request $request): void
    {
        $this->authorizePermission('reports.view');

        $query = trim((string) $request->input('query', ''));
        $rows = $this->fetchInventoryRows($query);

        $this->render('reports.inventory', [
            'title' => 'Inventory Report',
            'rows' => $rows,
            'query' => $query,
            'flash' => consume_flash(),
        ]);
    }

    public function expenses(Request $request): void
    {
        $this->authorizePermission('reports.view');

        $fromDate = $this->normalizeDate((string) $request->input('from_date', ''));
        $toDate = $this->normalizeDate((string) $request->input('to_date', ''));
        $rows = $this->fetchExpenseRows($fromDate, $toDate);

        $this->render('reports.expenses', [
            'title' => 'Expense Report',
            'rows' => $rows,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'flash' => consume_flash(),
        ]);
    }

    public function exportSalesCsv(Request $request): void
    {
        $this->authorizeAnyPermission(['reports.export', 'reports.view']);

        $fromDate = $this->normalizeDate((string) $request->input('from_date', ''));
        $toDate = $this->normalizeDate((string) $request->input('to_date', ''));
        $rows = $this->fetchSalesRows($fromDate, $toDate);

        $csvRows = [];
        foreach ($rows as $row) {
            $csvRows[] = [
                (string) ($row['report_date'] ?? ''),
                (string) ((int) ($row['transaction_count'] ?? 0)),
                (string) ((float) ($row['gross_sales'] ?? 0)),
            ];
        }

        log_activity('report.export.sales', ['from_date' => $fromDate, 'to_date' => $toDate, 'rows' => count($csvRows)]);
        $this->streamCsv('sales-report-' . date('Ymd-His') . '.csv', ['Date', 'Transactions', 'Gross Sales'], $csvRows);
    }

    public function exportInventoryCsv(Request $request): void
    {
        $this->authorizeAnyPermission(['reports.export', 'reports.view']);

        $query = trim((string) $request->input('query', ''));
        $rows = $this->fetchInventoryRows($query);

        $csvRows = [];
        foreach ($rows as $row) {
            $csvRows[] = [
                (string) ($row['sku'] ?? ''),
                (string) ($row['product_name'] ?? ''),
                (string) ((int) ($row['quantity_on_hand'] ?? 0)),
                (string) ((int) ($row['reorder_level'] ?? 0)),
                (string) ((float) ($row['stock_value'] ?? 0)),
            ];
        }

        log_activity('report.export.inventory', ['query' => $query, 'rows' => count($csvRows)]);
        $this->streamCsv('inventory-report-' . date('Ymd-His') . '.csv', ['SKU', 'Product', 'QOH', 'Reorder Level', 'Stock Value'], $csvRows);
    }

    public function exportExpensesCsv(Request $request): void
    {
        $this->authorizeAnyPermission(['reports.export', 'reports.view']);

        $fromDate = $this->normalizeDate((string) $request->input('from_date', ''));
        $toDate = $this->normalizeDate((string) $request->input('to_date', ''));
        $rows = $this->fetchExpenseRows($fromDate, $toDate);

        $csvRows = [];
        foreach ($rows as $row) {
            $csvRows[] = [
                (string) ($row['report_date'] ?? ''),
                (string) ((int) ($row['entry_count'] ?? 0)),
                (string) ((float) ($row['total_expense'] ?? 0)),
            ];
        }

        log_activity('report.export.expenses', ['from_date' => $fromDate, 'to_date' => $toDate, 'rows' => count($csvRows)]);
        $this->streamCsv('expenses-report-' . date('Ymd-His') . '.csv', ['Date', 'Entries', 'Total Expense'], $csvRows);
    }

    private function fetchSalesRows(?string $fromDate, ?string $toDate): array
    {
        $pdo = Database::connection();
        $whereParts = [];
        $params = [];

        if ($fromDate !== null) {
            $whereParts[] = 'DATE(created_at) >= :from_date';
            $params['from_date'] = $fromDate;
        }
        if ($toDate !== null) {
            $whereParts[] = 'DATE(created_at) <= :to_date';
            $params['to_date'] = $toDate;
        }

        $sql = 'SELECT DATE(created_at) AS report_date,
                       COUNT(*) AS transaction_count,
                       COALESCE(SUM(total), 0) AS gross_sales
                FROM sales';

        if (count($whereParts) > 0) {
            $sql .= ' WHERE ' . implode(' AND ', $whereParts);
        }

        $sql .= ' GROUP BY DATE(created_at)
                  ORDER BY report_date DESC
                  LIMIT 180';

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    private function fetchInventoryRows(string $query): array
    {
        $pdo = Database::connection();
        $sql = 'SELECT p.sku, p.product_name, i.quantity_on_hand, i.reorder_level,
                       (i.quantity_on_hand * p.cost_price) AS stock_value
                FROM inventory i
                INNER JOIN products p ON p.id = i.product_id';
        $params = [];

        if ($query !== '') {
            $sql .= ' WHERE p.sku LIKE :query OR p.product_name LIKE :query';
            $params['query'] = '%' . $query . '%';
        }

        $sql .= ' ORDER BY p.product_name';

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    private function fetchExpenseRows(?string $fromDate, ?string $toDate): array
    {
        $pdo = Database::connection();
        $whereParts = [];
        $params = [];

        if ($fromDate !== null) {
            $whereParts[] = 'DATE(expense_date) >= :from_date';
            $params['from_date'] = $fromDate;
        }
        if ($toDate !== null) {
            $whereParts[] = 'DATE(expense_date) <= :to_date';
            $params['to_date'] = $toDate;
        }

        $sql = 'SELECT DATE(expense_date) AS report_date,
                       COUNT(*) AS entry_count,
                       COALESCE(SUM(amount), 0) AS total_expense
                FROM expenses';

        if (count($whereParts) > 0) {
            $sql .= ' WHERE ' . implode(' AND ', $whereParts);
        }

        $sql .= ' GROUP BY DATE(expense_date)
                 ORDER BY report_date DESC
                 LIMIT 180';

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    private function normalizeDate(string $rawDate): ?string
    {
        $rawDate = trim($rawDate);
        if ($rawDate === '') {
            return null;
        }

        $date = \DateTime::createFromFormat('Y-m-d', $rawDate);
        if (!$date || $date->format('Y-m-d') !== $rawDate) {
            return null;
        }

        return $rawDate;
    }

    private function streamCsv(string $filename, array $header, array $rows): void
    {
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'wb');
        if ($output === false) {
            http_response_code(500);
            exit('Unable to stream CSV');
        }

        fputcsv($output, $header);
        foreach ($rows as $row) {
            fputcsv($output, $row);
        }

        fclose($output);
        exit;
    }
}
