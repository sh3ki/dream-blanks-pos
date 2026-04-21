<?php
$filterConfig = [
    'targetTableId' => 'transactions-table',
    'searchPlaceholder' => 'Search transactions by number or reference...',
    'searchColumns' => [0, 4],
    'filterLabel' => 'Transaction Type',
    'filterColumn' => 1,
    'filterOptions' => [
        ['value' => 'SALE', 'label' => 'SALE'],
        ['value' => 'EXPENSE', 'label' => 'EXPENSE'],
        ['value' => 'PAYMENT', 'label' => 'PAYMENT'],
        ['value' => 'REFUND', 'label' => 'REFUND'],
        ['value' => 'ADJUSTMENT', 'label' => 'ADJUSTMENT'],
    ],
    'dateColumn' => 5,
    'emptyMessage' => 'No transactions match your filters.',
];
require VIEW_PATH . '/components/list_filters.php';
?>

<div class="card">
    <h3 style="margin-top:0;">Transaction History</h3>
    <div class="table-wrap">
        <table class="table" id="transactions-table">
            <thead>
            <tr>
                <th>Transaction #</th>
                <th>Type</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Reference</th>
                <th>Date</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($transactions as $transaction): ?>
                <?php
                $statusClass = $transaction['status'] === 'Completed' ? 'status-ok' : ($transaction['status'] === 'Pending' ? 'status-low' : 'status-out');
                ?>
                <tr>
                    <td><?= e($transaction['transaction_number']) ?></td>
                    <td><?= e($transaction['transaction_type']) ?></td>
                    <td><?= number_format((float) $transaction['amount'], 2) ?></td>
                    <td><span class="status <?= $statusClass ?>"><?= e($transaction['status']) ?></span></td>
                    <td><?= e($transaction['reference_id']) ?></td>
                    <td><?= e($transaction['transaction_date']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
