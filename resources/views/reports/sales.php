<div class="card">
    <h3 style="margin-top:0;">Sales Report</h3>
    <div class="table-wrap">
        <table class="table">
            <thead>
            <tr>
                <th>Date</th>
                <th>Transactions</th>
                <th>Gross Sales</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td><?= e($row['report_date']) ?></td>
                    <td><?= (int) $row['transaction_count'] ?></td>
                    <td><?= number_format((float) $row['gross_sales'], 2) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
