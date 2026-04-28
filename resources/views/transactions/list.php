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
    'enableClientPaging' => false,
];
?>

<div class="page-header">
    <div>
        <h2 class="page-title">Transactions</h2>
        <p class="page-subtitle">Track all financial events across modules.</p>
    </div>
</div>

<?php require VIEW_PATH . '/components/list_filters.php'; ?>

<div class="card">
    <h3 class="card-title">Transaction History</h3>
    <div class="table-wrap">
        <table class="table" id="transactions-table" data-table>
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

    <?php if (($totalPages ?? 1) > 1): ?>
        <?php
        $prevPage = max(1, ((int) ($page ?? 1)) - 1);
        $nextPage = min((int) ($totalPages ?? 1), ((int) ($page ?? 1)) + 1);
        $lastPage = (int) ($totalPages ?? 1);
        ?>
        <div class="table-pagination">
            <div>Page <?= (int) ($page ?? 1) ?> of <?= (int) ($totalPages ?? 1) ?> (<?= (int) ($total ?? 0) ?> records)</div>
            <div class="pagination">
                <a class="btn btn-ghost btn-sm <?= (int) ($page ?? 1) <= 1 ? 'hidden' : '' ?>" href="<?= e(url('/transactions?page=1')) ?>">First</a>
                <a class="btn btn-ghost btn-sm <?= (int) ($page ?? 1) <= 1 ? 'hidden' : '' ?>" href="<?= e(url('/transactions?page=' . $prevPage)) ?>">Prev</a>
                <form class="page-jump" method="get" action="<?= e(url('/transactions')) ?>">
                    <input class="input input-sm" type="number" min="1" max="<?= (int) ($totalPages ?? 1) ?>" name="page" value="<?= (int) ($page ?? 1) ?>">
                    <span class="page-total">of <?= (int) ($totalPages ?? 1) ?></span>
                </form>
                <a class="btn btn-ghost btn-sm <?= (int) ($page ?? 1) >= (int) ($totalPages ?? 1) ? 'hidden' : '' ?>" href="<?= e(url('/transactions?page=' . $nextPage)) ?>">Next</a>
                <a class="btn btn-ghost btn-sm <?= (int) ($page ?? 1) >= (int) ($totalPages ?? 1) ? 'hidden' : '' ?>" href="<?= e(url('/transactions?page=' . $lastPage)) ?>">Last</a>
            </div>
        </div>
    <?php endif; ?>
</div>
