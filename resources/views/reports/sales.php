<?php
$dateOptions = [];
foreach ($rows as $row) {
    $date = (string) ($row['report_date'] ?? '');
    if ($date !== '') {
        $dateOptions[$date] = ['value' => $date, 'label' => $date];
    }
}

$filterConfig = [
    'targetTableId' => 'sales-report-table',
    'searchPlaceholder' => 'Search sales report by date...',
    'searchColumns' => [0],
    'filterLabel' => 'Date',
    'filterColumn' => 0,
    'filterOptions' => array_values($dateOptions),
    'dateColumn' => 0,
    'emptyMessage' => 'No sales report rows match your filters.',
];
require VIEW_PATH . '/components/list_filters.php';
?>

<div class="card">
    <h3 style="margin-top:0;">Sales Report</h3>
    <div class="table-wrap">
        <table class="table" id="sales-report-table">
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
