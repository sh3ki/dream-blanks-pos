<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Services\SalesService;

class SalesController extends Controller
{
    public function pos(Request $request): void
    {
        $this->authorize(['Admin', 'Manager', 'Cashier']);

        $pdo = Database::connection();
        $products = $pdo->query(
            'SELECT p.id, p.sku, p.product_name, p.selling_price, c.category_name,
                    COALESCE(i.quantity_on_hand, 0) AS quantity_on_hand
             FROM products p
             LEFT JOIN categories c ON c.id = p.category_id
             LEFT JOIN inventory i ON i.product_id = p.id
             WHERE p.is_active = 1
             ORDER BY p.product_name'
        )->fetchAll();

        $categories = $pdo->query('SELECT id, category_name FROM categories WHERE is_active = 1 ORDER BY category_name')->fetchAll();

        $this->render('sales.pos', [
            'title' => 'POS',
            'products' => $products,
            'categories' => $categories,
            'flash' => consume_flash(),
        ]);
    }

    public function store(Request $request): void
    {
        $this->authorize(['Admin', 'Manager', 'Cashier']);

        $items = json_decode((string) $request->input('items_json', '[]'), true);
        if (!is_array($items) || count($items) === 0) {
            flash('error', 'Please add items to cart before checkout.');
            $this->redirect('/pos');
        }

        $payload = [
            'items' => $items,
            'discount' => (float) $request->input('discount', 0),
            'tax' => (float) $request->input('tax', 0),
            'amount_paid' => (float) $request->input('amount_paid', 0),
            'payment_method' => (string) $request->input('payment_method', 'Cash'),
            'notes' => $request->input('notes'),
            'reference_number' => $request->input('reference_number'),
        ];

        $service = new SalesService();

        try {
            $saleId = $service->createSale($payload, (int) Auth::id());
            flash('success', 'Sale completed successfully. Sale ID: ' . $saleId);
        } catch (\Throwable $e) {
            flash('error', 'Sale failed: ' . $e->getMessage());
        }

        $this->redirect('/sales');
    }

    public function index(Request $request): void
    {
        $this->authorize(['Admin', 'Manager', 'Cashier', 'Accountant']);

        $pdo = Database::connection();
        $sales = $pdo->query(
            'SELECT s.*, CONCAT(u.first_name, " ", u.last_name) AS cashier_name
             FROM sales s
             INNER JOIN users u ON u.id = s.cashier_id
             ORDER BY s.created_at DESC
             LIMIT 200'
        )->fetchAll();

        $this->render('sales.list', [
            'title' => 'Sales',
            'sales' => $sales,
            'flash' => consume_flash(),
        ]);
    }
}
