<?php

namespace App\Services;

use App\Core\Database;

class InventoryService
{
    public function adjustStock(int $productId, int $newQty, string $reason, int $userId): void
    {
        $pdo = Database::connection();
        $pdo->beginTransaction();

        try {
            $inventoryStmt = $pdo->prepare('SELECT * FROM inventory WHERE product_id = :product_id LIMIT 1');
            $inventoryStmt->execute(['product_id' => $productId]);
            $inventory = $inventoryStmt->fetch();

            $oldQty = (int) ($inventory['quantity_on_hand'] ?? 0);
            $difference = $newQty - $oldQty;

            if ($inventory) {
                $update = $pdo->prepare('UPDATE inventory SET quantity_on_hand = :qty, updated_at = NOW() WHERE product_id = :product_id');
                $update->execute(['qty' => $newQty, 'product_id' => $productId]);
            } else {
                $insert = $pdo->prepare(
                    'INSERT INTO inventory (product_id, quantity_on_hand, quantity_reserved, reorder_level, reorder_quantity, created_at, updated_at)
                     VALUES (:product_id, :qty, 0, 10, 20, NOW(), NOW())'
                );
                $insert->execute(['product_id' => $productId, 'qty' => $newQty]);
            }

            $movementType = $difference >= 0 ? 'IN' : 'OUT';
            $movement = $pdo->prepare(
                'INSERT INTO stock_movements (product_id, movement_type, quantity, reason, created_by, notes, created_at)
                 VALUES (:product_id, :movement_type, :quantity, :reason, :created_by, :notes, NOW())'
            );
            $movement->execute([
                'product_id' => $productId,
                'movement_type' => $movementType,
                'quantity' => abs($difference),
                'reason' => $reason,
                'created_by' => $userId,
                'notes' => 'Manual stock adjustment',
            ]);

            $history = $pdo->prepare(
                'INSERT INTO stock_history (product_id, old_quantity, new_quantity, difference, reason, created_by, created_at)
                 VALUES (:product_id, :old_quantity, :new_quantity, :difference, :reason, :created_by, NOW())'
            );
            $history->execute([
                'product_id' => $productId,
                'old_quantity' => $oldQty,
                'new_quantity' => $newQty,
                'difference' => $difference,
                'reason' => $reason,
                'created_by' => $userId,
            ]);

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public function getLowStockItems(): array
    {
        $pdo = Database::connection();
        $stmt = $pdo->query(
            'SELECT p.id, p.sku, p.product_name, i.quantity_on_hand, i.reorder_level
             FROM products p
             INNER JOIN inventory i ON i.product_id = p.id
             WHERE p.is_active = 1 AND i.quantity_on_hand <= i.reorder_level
             ORDER BY i.quantity_on_hand ASC'
        );

        return $stmt->fetchAll();
    }
}
