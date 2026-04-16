<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Services\InventoryService;

class InventoryController extends Controller
{
    public function index(Request $request): void
    {
        $this->authorize(['Admin', 'Manager', 'Store Staff', 'Cashier']);

        $pdo = Database::connection();
        $items = $pdo->query(
            'SELECT p.id AS product_id, p.sku, p.product_name, i.quantity_on_hand, i.quantity_reserved,
                    i.reorder_level, i.reorder_quantity,
                    (i.quantity_on_hand - i.quantity_reserved) AS quantity_available
             FROM inventory i
             INNER JOIN products p ON p.id = i.product_id
             ORDER BY p.product_name'
        )->fetchAll();

        $this->render('inventory.list', [
            'title' => 'Inventory',
            'items' => $items,
            'flash' => consume_flash(),
        ]);
    }

    public function adjust(Request $request): void
    {
        $this->authorize(['Admin', 'Manager', 'Store Staff']);

        $productId = (int) $request->input('product_id');
        $newQty = (int) $request->input('new_quantity');
        $reason = trim((string) $request->input('reason'));

        if ($productId < 1 || $reason === '') {
            flash('error', 'Product and reason are required for stock adjustment.');
            $this->redirect('/inventory');
        }

        $service = new InventoryService();
        $service->adjustStock($productId, $newQty, $reason, (int) Auth::id());

        flash('success', 'Stock adjusted successfully.');
        $this->redirect('/inventory');
    }

    public function lowStock(Request $request): void
    {
        $this->authorize(['Admin', 'Manager', 'Store Staff']);
        $service = new InventoryService();

        $this->json([
            'status' => 'success',
            'message' => 'Low stock items fetched.',
            'data' => $service->getLowStockItems(),
        ]);
    }
}
