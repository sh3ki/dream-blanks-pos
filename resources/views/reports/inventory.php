<?php
$query = $query ?? '';
$exportQuery = http_build_query(array_filter([
    'query' => $query,
], static fn ($value) => $value !== null && $value !== ''));

$filterConfig = [
    'targetTableId' => 'inventory-report-table',
    'searchPlaceholder' => 'Search inventory report by SKU or product...',
    'searchColumns' => [0, 1],
    'filterLabel' => 'Stock Health',
    'filterColumn' => 4,
    'filterOptions' => [
        ['value' => 'Healthy', 'label' => 'Healthy'],
        ['value' => 'Low', 'label' => 'Low'],
        ['value' => 'Out', 'label' => 'Out'],
    ],
    'dateColumn' => '',
    'emptyMessage' => 'No inventory report rows match your filters.',
];
?>

<div class="page-header">
    <div>
        <h2 class="page-title">Inventory Report</h2>
        <p class="page-subtitle">Analyze stock health and valuation snapshots.</p>
    </div>
</div>

<?php require VIEW_PATH . '/components/list_filters.php'; ?>

<div class="card">
    <h3 class="card-title">Report Filters</h3>
    <form method="get" action="<?= e(url('/reports/inventory')) ?>" class="grid grid-3">
        <div class="field field-span-2">
            <label>Search Product / SKU</label>
            <input class="input" type="text" name="query" value="<?= e((string) $query) ?>" placeholder="Search SKU or product name...">
        </div>
        <div class="field field-actions">
            <button class="btn btn-primary" type="submit">Apply</button>
            <a class="btn btn-secondary" href="<?= e(url('/reports/inventory')) ?>">Reset</a>
            <a class="btn btn-success" href="<?= e(url('/reports/inventory/export' . ($exportQuery !== '' ? '?' . $exportQuery : ''))) ?>">Export CSV</a>
        </div>
    </form>
</div>

<div class="card">
    <h3 class="card-title">Inventory Report</h3>
    <div class="table-wrap">
        <table class="table" id="inventory-report-table" data-table>
            <thead>
            <tr>
                <th>SKU</th>
                <th>Product</th>
                <th>QOH</th>
                <th>Reorder Level</th>
                <th>Status</th>
                <th>Stock Value</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $row): ?>
                <?php
                $qoh = (int) $row['quantity_on_hand'];
                $reorder = (int) $row['reorder_level'];
                $statusClass = $qoh <= 0 ? 'status-out' : ($qoh <= $reorder ? 'status-low' : 'status-ok');
                $statusText = $qoh <= 0 ? 'Out' : ($qoh <= $reorder ? 'Low' : 'Healthy');
                ?>
                <tr>
                    <td><?= e($row['sku']) ?></td>
                    <td><?= e($row['product_name']) ?></td>
                    <td><?= (int) $row['quantity_on_hand'] ?></td>
                    <td><?= (int) $row['reorder_level'] ?></td>
                    <td><span class="status <?= $statusClass ?>"><?= $statusText ?></span></td>
                    <td><?= number_format((float) $row['stock_value'], 2) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="table-pagination" data-table-pagination data-target-table="inventory-report-table">
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
