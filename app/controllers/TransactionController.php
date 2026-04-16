<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;

class TransactionController extends Controller
{
    public function index(Request $request): void
    {
        $this->authorize(['Admin', 'Manager', 'Accountant']);

        $pdo = Database::connection();
        $transactions = $pdo->query('SELECT * FROM transactions ORDER BY transaction_date DESC LIMIT 200')->fetchAll();

        $this->render('transactions.list', [
            'title' => 'Transaction History',
            'transactions' => $transactions,
            'flash' => consume_flash(),
        ]);
    }
}
