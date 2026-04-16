<div class="card">
    <h3 style="margin-top:0;">Sales Transactions</h3>
    <div class="table-wrap">
        <table class="table">
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
</div>
