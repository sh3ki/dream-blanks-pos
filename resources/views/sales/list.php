<?php
$filterConfig = [
    'targetTableId' => 'sales-table',
    'searchPlaceholder' => 'Search sales by transaction, cashier, or payment method...',
    'searchColumns' => [0, 1, 2],
    'filterLabel' => 'Payment Method',
    'filterColumn' => 2,
    'filterOptions' => [
        ['value' => 'Cash', 'label' => 'Cash'],
        ['value' => 'Credit Card', 'label' => 'Credit Card'],
        ['value' => 'Debit Card', 'label' => 'Debit Card'],
        ['value' => 'Digital Wallet', 'label' => 'Digital Wallet'],
        ['value' => 'Check', 'label' => 'Check'],
        ['value' => 'Bank Transfer', 'label' => 'Bank Transfer'],
    ],
    'dateColumn' => 6,
    'emptyMessage' => 'No sales transactions match your filters.',
    'enableClientPaging' => false,
];
?>

<div class="page-header">
    <div>
        <h2 class="page-title">Sales</h2>
        <p class="page-subtitle">Review transaction activity and payment mix.</p>
    </div>
</div>

<?php require VIEW_PATH . '/components/list_filters.php'; ?>

<div class="card">
    <h3 class="card-title">Sales Transactions</h3>
    <div class="table-wrap">
        <table class="table" id="sales-table" data-table>
            <thead>
            <tr>
                <th>Transaction #</th>
                <th>Cashier</th>
                <th>Payment Method</th>
                <th>Total</th>
                <th>Amount Paid</th>
                <th>Status</th>
                <th>Date</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($sales as $sale): ?>
                <tr>
                    <td><?= e($sale['transaction_number']) ?></td>
                    <td><?= e($sale['cashier_name']) ?></td>
                    <td><?= e($sale['payment_method']) ?></td>
                    <td><?= number_format((float) $sale['total'], 2) ?></td>
                    <td><?= number_format((float) $sale['amount_paid'], 2) ?></td>
                    <td><span class="status status-ok"><?= e($sale['status']) ?></span></td>
                    <td><?= e($sale['created_at']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if (($totalPages ?? 1) > 1): ?>
        <?php
        $prevPage = max(1, ((int) ($page ?? 1)) - 1);
        $nextPage = min((int) ($totalPages ?? 1), ((int) ($page ?? 1)) + 1);
        $lastPage = (int) ($totalPages ?? 1);
        ?>
        <div class="table-pagination">
            <div>Page <?= (int) ($page ?? 1) ?> of <?= (int) ($totalPages ?? 1) ?> (<?= (int) ($total ?? 0) ?> records)</div>
            <div class="pagination">
                <a class="btn btn-ghost btn-sm <?= (int) ($page ?? 1) <= 1 ? 'hidden' : '' ?>" href="<?= e(url('/sales?page=1')) ?>">First</a>
                <a class="btn btn-ghost btn-sm <?= (int) ($page ?? 1) <= 1 ? 'hidden' : '' ?>" href="<?= e(url('/sales?page=' . $prevPage)) ?>">Prev</a>
                <form class="page-jump" method="get" action="<?= e(url('/sales')) ?>">
                    <input class="input input-sm" type="number" min="1" max="<?= (int) ($totalPages ?? 1) ?>" name="page" value="<?= (int) ($page ?? 1) ?>">
                    <span class="page-total">of <?= (int) ($totalPages ?? 1) ?></span>
                </form>
                <a class="btn btn-ghost btn-sm <?= (int) ($page ?? 1) >= (int) ($totalPages ?? 1) ? 'hidden' : '' ?>" href="<?= e(url('/sales?page=' . $nextPage)) ?>">Next</a>
                <a class="btn btn-ghost btn-sm <?= (int) ($page ?? 1) >= (int) ($totalPages ?? 1) ? 'hidden' : '' ?>" href="<?= e(url('/sales?page=' . $lastPage)) ?>">Last</a>
            </div>
        </div>
    <?php endif; ?>
</div>
