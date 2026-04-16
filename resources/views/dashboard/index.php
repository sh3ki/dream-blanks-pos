<div class="grid grid-3">
    <div class="card">
        <div class="kpi-label">Today's Sales</div>
        <div class="kpi-value"><?= number_format((float) $summary['today_sales'], 2) ?></div>
    </div>
    <div class="card">
        <div class="kpi-label">Today's Transactions</div>
        <div class="kpi-value"><?= (int) $summary['today_transactions'] ?></div>
    </div>
    <div class="card">
        <div class="kpi-label">Average Ticket</div>
        <div class="kpi-value"><?= number_format((float) $summary['average_ticket'], 2) ?></div>
    </div>
    <div class="card">
        <div class="kpi-label">Today's Expenses</div>
        <div class="kpi-value"><?= number_format((float) $summary['today_expenses'], 2) ?></div>
    </div>
    <div class="card">
        <div class="kpi-label">Low Stock Items</div>
        <div class="kpi-value"><?= (int) $summary['low_stock_items'] ?></div>
    </div>
    <div class="card">
        <div class="kpi-label">Quick Actions</div>
        <div class="flex flex-gap" style="margin-top:10px;flex-wrap:wrap;">
            <a class="btn btn-primary" href="<?= e(url('/pos')) ?>">New Sale</a>
            <a class="btn btn-secondary" href="<?= e(url('/products')) ?>">Add Product</a>
            <a class="btn btn-secondary" href="<?= e(url('/expenses')) ?>">Record Expense</a>
        </div>
    </div>
</div>

<div class="card">
    <h3 style="margin-top:0;">Recent Sales</h3>
    <div class="table-wrap">
        <table class="table">
            <thead>
            <tr>
                <th>Transaction #</th>
                <th>Cashier</th>
                <th>Payment</th>
                <th>Total</th>
                <th>Date</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($recentSales as $sale): ?>
                <tr>
                    <td><?= e($sale['transaction_number']) ?></td>
                    <td><?= e($sale['cashier_name']) ?></td>
                    <td><?= e($sale['payment_method']) ?></td>
                    <td><?= number_format((float) $sale['total'], 2) ?></td>
                    <td><?= e($sale['created_at']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
