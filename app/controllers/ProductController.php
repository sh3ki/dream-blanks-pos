<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;

class ProductController extends Controller
{
    public function index(Request $request): void
    {
        $this->authorize(['Admin', 'Manager', 'Store Staff', 'Cashier']);

        $search = trim((string) $request->input('search', ''));
        $pdo = Database::connection();

        $sql = 'SELECT p.*, c.category_name, COALESCE(i.quantity_on_hand, 0) AS quantity_on_hand, COALESCE(i.reorder_level, 0) AS reorder_level
                FROM products p
                LEFT JOIN categories c ON c.id = p.category_id
                LEFT JOIN inventory i ON i.product_id = p.id';

        $params = [];
        if ($search !== '') {
            $sql .= ' WHERE p.product_name LIKE :search OR p.sku LIKE :search';
            $params['search'] = '%' . $search . '%';
        }

        $sql .= ' ORDER BY p.created_at DESC';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $products = $stmt->fetchAll();

        $categories = $pdo->query('SELECT id, category_name FROM categories WHERE is_active = 1 ORDER BY category_name')->fetchAll();

        $this->render('products.list', [
            'title' => 'Products',
            'products' => $products,
            'categories' => $categories,
            'search' => $search,
            'flash' => consume_flash(),
        ]);
    }

    public function store(Request $request): void
    {
        $this->authorize(['Admin', 'Manager', 'Store Staff']);

        $productName = trim((string) $request->input('product_name'));
        $sku = trim((string) $request->input('sku'));
        $sellingPrice = (float) $request->input('selling_price');

        if ($productName === '' || $sku === '' || $sellingPrice <= 0) {
            flash('error', 'Product name, SKU, and selling price are required.');
            $this->redirect('/products');
        }

        $pdo = Database::connection();
        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare(
                'INSERT INTO products (sku, product_name, description, category_id, cost_price, selling_price, wholesale_price, unit_of_measurement, is_active, tax_treatment, commission_rate, created_at, updated_at)
                 VALUES (:sku, :product_name, :description, :category_id, :cost_price, :selling_price, :wholesale_price, :unit_of_measurement, :is_active, :tax_treatment, :commission_rate, NOW(), NOW())'
            );
            $stmt->execute([
                'sku' => $sku,
                'product_name' => $productName,
                'description' => $request->input('description'),
                'category_id' => $request->input('category_id') ?: null,
                'cost_price' => (float) $request->input('cost_price', 0),
                'selling_price' => $sellingPrice,
                'wholesale_price' => (float) $request->input('wholesale_price', 0),
                'unit_of_measurement' => $request->input('unit_of_measurement', 'Piece'),
                'is_active' => $request->input('is_active') ? 1 : 0,
                'tax_treatment' => $request->input('tax_treatment', 'Taxable'),
                'commission_rate' => (float) $request->input('commission_rate', 0),
            ]);

            $productId = (int) $pdo->lastInsertId();

            $inventoryStmt = $pdo->prepare(
                'INSERT INTO inventory (product_id, quantity_on_hand, quantity_reserved, reorder_level, reorder_quantity, created_at, updated_at)
                 VALUES (:product_id, :quantity_on_hand, 0, :reorder_level, :reorder_quantity, NOW(), NOW())'
            );
            $inventoryStmt->execute([
                'product_id' => $productId,
                'quantity_on_hand' => (int) $request->input('initial_stock', 0),
                'reorder_level' => (int) $request->input('reorder_level', 10),
                'reorder_quantity' => (int) $request->input('reorder_quantity', 20),
            ]);

            $pdo->commit();
            flash('success', 'Product created.');
        } catch (\Throwable $e) {
            $pdo->rollBack();
            flash('error', 'Unable to create product: ' . $e->getMessage());
        }

        $this->redirect('/products');
    }

    public function apiList(Request $request): void
    {
        $this->authorize(['Admin', 'Manager', 'Store Staff', 'Cashier']);
        $query = trim((string) $request->input('query', ''));

        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'SELECT p.id, p.sku, p.product_name, p.selling_price, COALESCE(i.quantity_on_hand, 0) AS quantity_on_hand
             FROM products p
             LEFT JOIN inventory i ON i.product_id = p.id
             WHERE p.is_active = 1 AND (p.product_name LIKE :query OR p.sku LIKE :query)
             ORDER BY p.product_name ASC
             LIMIT 20'
        );
        $stmt->execute(['query' => '%' . $query . '%']);

        $this->json([
            'status' => 'success',
            'message' => 'Products fetched.',
            'data' => $stmt->fetchAll(),
        ]);
    }
}
