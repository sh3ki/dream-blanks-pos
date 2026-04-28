<?php
$fromDate = $fromDate ?? '';
$toDate = $toDate ?? '';
$exportQuery = http_build_query(array_filter([
    'from_date' => $fromDate,
    'to_date' => $toDate,
], static fn ($value) => $value !== null && $value !== ''));

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
?>

<div class="page-header">
    <div>
        <h2 class="page-title">Sales Report</h2>
        <p class="page-subtitle">Review daily sales and export summaries.</p>
    </div>
</div>

<?php require VIEW_PATH . '/components/list_filters.php'; ?>

<div class="card">
    <h3 class="card-title">Report Filters</h3>
    <form method="get" action="<?= e(url('/reports/sales')) ?>" class="grid grid-3">
        <div class="field">
            <label>From Date</label>
            <input class="input" type="date" name="from_date" value="<?= e((string) $fromDate) ?>">
        </div>
        <div class="field">
            <label>To Date</label>
            <input class="input" type="date" name="to_date" value="<?= e((string) $toDate) ?>">
        </div>
        <div class="field field-actions">
            <button class="btn btn-primary" type="submit">Apply</button>
            <a class="btn btn-secondary" href="<?= e(url('/reports/sales')) ?>">Reset</a>
            <a class="btn btn-success" href="<?= e(url('/reports/sales/export' . ($exportQuery !== '' ? '?' . $exportQuery : ''))) ?>">Export CSV</a>
        </div>
    </form>
</div>

<div class="card">
    <h3 class="card-title">Sales Report</h3>
    <div class="table-wrap">
        <table class="table" id="sales-report-table" data-table>
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

    <div class="table-pagination" data-table-pagination data-target-table="sales-report-table">
        <div data-table-page-info></div>
        <div class="pagination">
            <button class="btn btn-ghost btn-sm" type="button" data-table-page-first>First</button>
            <button class="btn btn-ghost btn-sm" type="button" data-table-page-prev>Prev</button>
            <div class="page-jump">
                <input class="input input-sm" type="number" min="1" data-table-page-input>
                <span class="page-total" data-table-page-total></span>
            </div>
            <button class="btn btn-ghost btn-sm" type="button" data-table-page-next>Next</button>
            <button class="btn btn-ghost btn-sm" type="button" data-table-page-last>Last</button>
        </div>
    </div>
</div>
