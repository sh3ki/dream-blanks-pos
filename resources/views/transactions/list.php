<div class="card">
    <h3 style="margin-top:0;">Transaction History</h3>
    <div class="table-wrap">
        <table class="table">
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
