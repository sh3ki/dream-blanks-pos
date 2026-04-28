<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;

class TransactionController extends Controller
{
    public function index(Request $request): void
    {
        $this->authorizePermission('transactions.view');

        $page = max(1, (int) $request->input('page', 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        $pdo = Database::connection();

        $countRow = $pdo->query('SELECT COUNT(*) AS total FROM transactions')->fetch();
        $total = (int) ($countRow['total'] ?? 0);
        $totalPages = max(1, (int) ceil($total / $perPage));
        if ($page > $totalPages) {
            $page = $totalPages;
            $offset = ($page - 1) * $perPage;
        }

        $stmt = $pdo->prepare('SELECT * FROM transactions ORDER BY transaction_date DESC LIMIT :limit OFFSET :offset');
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        $transactions = $stmt->fetchAll();

        $this->render('transactions.list', [
            'title' => 'Transaction History',
            'transactions' => $transactions,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'totalPages' => $totalPages,
            'flash' => consume_flash(),
        ]);
    }
}
