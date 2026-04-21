<?php
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
require VIEW_PATH . '/components/list_filters.php';
?>

<div class="card">
    <h3 style="margin-top:0;">Inventory Report</h3>
    <div class="table-wrap">
        <table class="table" id="inventory-report-table">
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
</div>
