<div class="card">
    <h3 style="margin-top:0;">Expense Report</h3>
    <div class="table-wrap">
        <table class="table">
            <thead>
            <tr>
                <th>Date</th>
                <th>Entries</th>
                <th>Total Expense</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td><?= e($row['report_date']) ?></td>
                    <td><?= (int) $row['entry_count'] ?></td>
                    <td><?= number_format((float) $row['total_expense'], 2) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
