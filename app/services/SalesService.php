<?php

namespace App\Services;

use App\Core\Database;

class SalesService
{
    public function createSale(array $payload, int $cashierId): int
    {
        $pdo = Database::connection();
        $pdo->beginTransaction();

        try {
            $subtotal = 0.0;
            foreach ($payload['items'] as $item) {
                $subtotal += ((float) $item['unit_price'] * (int) $item['quantity']) - (float) ($item['discount'] ?? 0);
            }

            $discount = (float) ($payload['discount'] ?? 0);
            $tax = (float) ($payload['tax'] ?? 0);
            $total = max(0, $subtotal - $discount + $tax);
            $amountPaid = (float) ($payload['amount_paid'] ?? $total);
            $change = max(0, $amountPaid - $total);

            $transactionNumber = 'TXN-' . date('Ymd') . '-' . str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT);

            $saleStmt = $pdo->prepare(
                'INSERT INTO sales (transaction_number, cashier_id, customer_id, payment_method, subtotal, discount, tax, total, amount_paid, `change`, payment_status, notes, status, created_at)
                 VALUES (:transaction_number, :cashier_id, :customer_id, :payment_method, :subtotal, :discount, :tax, :total, :amount_paid, :change, :payment_status, :notes, :status, NOW())'
            );
            $saleStmt->execute([
                'transaction_number' => $transactionNumber,
                'cashier_id' => $cashierId,
                'customer_id' => $payload['customer_id'] ?? null,
                'payment_method' => $payload['payment_method'],
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => $tax,
                'total' => $total,
                'amount_paid' => $amountPaid,
                'change' => $change,
                'payment_status' => 'Completed',
                'notes' => $payload['notes'] ?? null,
                'status' => 'Completed',
            ]);

            $saleId = (int) $pdo->lastInsertId();

            $itemStmt = $pdo->prepare(
                'INSERT INTO sale_items (sale_id, product_id, quantity, unit_price, discount, line_total, created_at)
                 VALUES (:sale_id, :product_id, :quantity, :unit_price, :discount, :line_total, NOW())'
            );

            $inventoryUpdate = $pdo->prepare('UPDATE inventory SET quantity_on_hand = quantity_on_hand - :quantity, updated_at = NOW() WHERE product_id = :product_id AND quantity_on_hand >= :quantity');
            $stockMove = $pdo->prepare(
                'INSERT INTO stock_movements (product_id, movement_type, quantity, reason, reference_id, created_by, notes, created_at)
                 VALUES (:product_id, :movement_type, :quantity, :reason, :reference_id, :created_by, :notes, NOW())'
            );

            foreach ($payload['items'] as $item) {
                $qty = (int) $item['quantity'];
                $lineTotal = ((float) $item['unit_price'] * $qty) - (float) ($item['discount'] ?? 0);

                $itemStmt->execute([
                    'sale_id' => $saleId,
                    'product_id' => (int) $item['product_id'],
                    'quantity' => $qty,
                    'unit_price' => (float) $item['unit_price'],
                    'discount' => (float) ($item['discount'] ?? 0),
                    'line_total' => $lineTotal,
                ]);

                $inventoryUpdate->execute([
                    'quantity' => $qty,
                    'product_id' => (int) $item['product_id'],
                ]);

                if ($inventoryUpdate->rowCount() === 0) {
                    throw new \RuntimeException('Insufficient stock for product ID ' . (int) $item['product_id']);
                }

                $stockMove->execute([
                    'product_id' => (int) $item['product_id'],
                    'movement_type' => 'OUT',
                    'quantity' => $qty,
                    'reason' => 'SALE',
                    'reference_id' => $saleId,
                    'created_by' => $cashierId,
                    'notes' => 'Stock deducted from POS sale',
                ]);
            }

            $paymentStmt = $pdo->prepare(
                'INSERT INTO payments (sale_id, payment_method, amount, reference_number, created_at)
                 VALUES (:sale_id, :payment_method, :amount, :reference_number, NOW())'
            );
            $paymentStmt->execute([
                'sale_id' => $saleId,
                'payment_method' => $payload['payment_method'],
                'amount' => $amountPaid,
                'reference_number' => $payload['reference_number'] ?? null,
            ]);

            $receiptStmt = $pdo->prepare(
                'INSERT INTO receipts (sale_id, receipt_number, receipt_date, printed_count, created_at)
                 VALUES (:sale_id, :receipt_number, NOW(), 0, NOW())'
            );
            $receiptStmt->execute([
                'sale_id' => $saleId,
                'receipt_number' => 'RCPT-' . date('Ymd') . '-' . str_pad((string) $saleId, 6, '0', STR_PAD_LEFT),
            ]);

            $pdo->commit();
            return $saleId;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
