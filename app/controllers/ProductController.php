<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;

class ProductController extends Controller
{
    public function index(Request $request): void
    {
        $this->authorizeAnyPermission(['products.view', 'products.manage']);

        $search = trim((string) $request->input('search', ''));
        $editProductId = max(0, (int) $request->input('edit_id', 0));
        $page = max(1, (int) $request->input('page', 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        $pdo = Database::connection();

        $sql = 'SELECT p.*, c.category_name, COALESCE(i.quantity_on_hand, 0) AS quantity_on_hand, COALESCE(i.reorder_level, 0) AS reorder_level
                FROM products p
                LEFT JOIN categories c ON c.id = p.category_id
                LEFT JOIN inventory i ON i.product_id = p.id';

        $countSql = 'SELECT COUNT(*) AS total
                     FROM products p
                     LEFT JOIN categories c ON c.id = p.category_id
                     LEFT JOIN inventory i ON i.product_id = p.id';

        $params = [];
        if ($search !== '') {
            $where = ' WHERE p.product_name LIKE :search OR p.sku LIKE :search';
            $sql .= $where;
            $countSql .= $where;
            $params['search'] = '%' . $search . '%';
        }

        $countStmt = $pdo->prepare($countSql);
        $countStmt->execute($params);
        $total = (int) ($countStmt->fetch()['total'] ?? 0);
        $totalPages = max(1, (int) ceil($total / $perPage));
        if ($page > $totalPages) {
            $page = $totalPages;
            $offset = ($page - 1) * $perPage;
        }

        $sql .= ' ORDER BY p.created_at DESC LIMIT :limit OFFSET :offset';
        $stmt = $pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value, \PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        $products = $stmt->fetchAll();

        $editProduct = null;
        if ($editProductId > 0) {
            foreach ($products as $productRow) {
                if ((int) ($productRow['id'] ?? 0) === $editProductId) {
                    $editProduct = $productRow;
                    break;
                }
            }

            if (!is_array($editProduct)) {
                $editStmt = $pdo->prepare(
                    'SELECT p.*, c.category_name, COALESCE(i.quantity_on_hand, 0) AS quantity_on_hand, COALESCE(i.reorder_level, 0) AS reorder_level,
                            COALESCE(i.reorder_quantity, 0) AS reorder_quantity
                     FROM products p
                     LEFT JOIN categories c ON c.id = p.category_id
                     LEFT JOIN inventory i ON i.product_id = p.id
                     WHERE p.id = :id
                     LIMIT 1'
                );
                $editStmt->execute(['id' => $editProductId]);
                $editProduct = $editStmt->fetch() ?: null;
            }
        }

        $categories = $pdo->query('SELECT id, category_name FROM categories WHERE is_active = 1 ORDER BY category_name')->fetchAll();

        $this->render('products.list', [
            'title' => 'Products',
            'products' => $products,
            'editProduct' => $editProduct,
            'categories' => $categories,
            'search' => $search,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'totalPages' => $totalPages,
            'flash' => consume_flash(),
        ]);
    }

    public function store(Request $request): void
    {
        $this->authorizeAnyPermission(['products.create', 'products.manage']);

        $productName = trim((string) $request->input('product_name'));
        $sku = trim((string) $request->input('sku'));
        $sellingPrice = (float) $request->input('selling_price');

        $imagePath = null;
        try {
            $imagePath = store_uploaded_image('product_image', 'products');
        } catch (\Throwable $e) {
            flash('error', $e->getMessage());
            $this->redirect('/products');
        }

        if ($productName === '' || $sku === '' || $sellingPrice <= 0) {
            flash('error', 'Product name, SKU, and selling price are required.');
            $this->redirect('/products');
        }

        $pdo = Database::connection();
        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare(
                'INSERT INTO products (sku, product_name, description, category_id, image_path, cost_price, selling_price, wholesale_price, unit_of_measurement, is_active, tax_treatment, commission_rate, created_at, updated_at)
                 VALUES (:sku, :product_name, :description, :category_id, :image_path, :cost_price, :selling_price, :wholesale_price, :unit_of_measurement, :is_active, :tax_treatment, :commission_rate, NOW(), NOW())'
            );
            $stmt->execute([
                'sku' => $sku,
                'product_name' => $productName,
                'description' => $request->input('description'),
                'category_id' => $request->input('category_id') ?: null,
                'image_path' => $imagePath,
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

    public function update(Request $request, array $params): void
    {
        $this->authorizeAnyPermission(['products.update', 'products.manage']);

        $productId = (int) ($params['id'] ?? 0);
        $productName = trim((string) $request->input('product_name'));
        $sku = trim((string) $request->input('sku'));
        $sellingPrice = (float) $request->input('selling_price');

        if ($productId < 1 || $productName === '' || $sku === '' || $sellingPrice <= 0) {
            flash('error', 'Product name, SKU, and selling price are required.');
            $this->redirect('/products?edit_id=' . $productId);
        }

        $imagePath = null;
        try {
            $imagePath = store_uploaded_image('product_image', 'products');
        } catch (\Throwable $e) {
            flash('error', $e->getMessage());
            $this->redirect('/products?edit_id=' . $productId);
        }

        $pdo = Database::connection();
        $pdo->beginTransaction();

        try {
            $existingSkuStmt = $pdo->prepare('SELECT id FROM products WHERE sku = :sku AND id <> :id LIMIT 1');
            $existingSkuStmt->execute([
                'sku' => $sku,
                'id' => $productId,
            ]);
            if ($existingSkuStmt->fetch()) {
                throw new \RuntimeException('SKU is already in use by another product.');
            }

            $sql = 'UPDATE products
                 SET sku = :sku,
                     product_name = :product_name,
                     description = :description,
                     category_id = :category_id,
                     cost_price = :cost_price,
                     selling_price = :selling_price,
                     wholesale_price = :wholesale_price,
                     unit_of_measurement = :unit_of_measurement,
                     is_active = :is_active,
                     tax_treatment = :tax_treatment,
                     commission_rate = :commission_rate';

            if ($imagePath !== null) {
                $sql .= ', image_path = :image_path';
            }

            $sql .= ', updated_at = NOW()
                 WHERE id = :id';

            $stmt = $pdo->prepare($sql);
            $params = [
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
                'id' => $productId,
            ];

            if ($imagePath !== null) {
                $params['image_path'] = $imagePath;
            }

            $stmt->execute($params);

            $inventoryStmt = $pdo->prepare(
                'UPDATE inventory
                 SET reorder_level = :reorder_level,
                     reorder_quantity = :reorder_quantity,
                     updated_at = NOW()
                 WHERE product_id = :product_id'
            );
            $inventoryStmt->execute([
                'reorder_level' => (int) $request->input('reorder_level', 10),
                'reorder_quantity' => (int) $request->input('reorder_quantity', 20),
                'product_id' => $productId,
            ]);

            $pdo->commit();
            log_activity('product.updated', ['product_id' => $productId]);
            flash('success', 'Product updated.');
        } catch (\Throwable $e) {
            $pdo->rollBack();
            flash('error', 'Unable to update product: ' . $e->getMessage());
            $this->redirect('/products?edit_id=' . $productId);
        }

        $this->redirect('/products');
    }

    public function apiList(Request $request): void
    {
        $this->authorizeAnyPermission(['products.view', 'products.manage']);
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

    public function toggleStatus(Request $request, array $params): void
    {
        $this->authorizeAnyPermission(['products.update', 'products.manage']);

        $productId = (int) ($params['id'] ?? 0);
        if ($productId < 1) {
            flash('error', 'Invalid product target.');
            $this->redirect('/products');
        }

        $pdo = Database::connection();

        try {
            $currentStmt = $pdo->prepare('SELECT is_active FROM products WHERE id = :id LIMIT 1');
            $currentStmt->execute(['id' => $productId]);
            $current = $currentStmt->fetch();

            if (!$current) {
                flash('error', 'Product not found.');
                $this->redirect('/products');
            }

            $newStatus = (int) $current['is_active'] === 1 ? 0 : 1;

            $stmt = $pdo->prepare('UPDATE products SET is_active = :is_active, updated_at = NOW() WHERE id = :id');
            $stmt->execute([
                'is_active' => $newStatus,
                'id' => $productId,
            ]);

            log_activity('product.status.updated', ['product_id' => $productId, 'is_active' => $newStatus]);
            flash('success', 'Product status updated successfully.');
        } catch (\Throwable $e) {
            flash('error', 'Unable to update product status: ' . $e->getMessage());
        }

        $this->redirect('/products');
    }
}
