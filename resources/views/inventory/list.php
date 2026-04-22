<?php
$filterConfig = [
    'targetTableId' => 'inventory-table',
    'searchPlaceholder' => 'Search inventory by SKU or product...',
    'searchColumns' => [0, 1],
    'filterLabel' => 'Stock Status',
    'filterColumn' => 6,
    'filterOptions' => [
        ['value' => 'Healthy', 'label' => 'Healthy'],
        ['value' => 'Low', 'label' => 'Low'],
        ['value' => 'Out', 'label' => 'Out'],
    ],
    'dateColumn' => '',
    'emptyMessage' => 'No inventory records match your filters.',
];
require VIEW_PATH . '/components/list_filters.php';
?>

<div class="card">
    <h3 style="margin-top:0;">Stock Adjustment</h3>
    <form method="post" action="<?= e(url('/inventory/adjust')) ?>">
        <?= csrf_field() ?>
        <div class="grid grid-3">
            <div class="field">
                <label>Product</label>
                <select class="select" name="product_id" required>
                    <option value="">Select Product</option>
                    <?php foreach ($items as $item): ?>
                        <option value="<?= (int) $item['product_id'] ?>"><?= e($item['sku'] . ' - ' . $item['product_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field"><label>New Quantity</label><input class="input" type="number" name="new_quantity" required></div>
            <div class="field"><label>Reason</label><input class="input" name="reason" placeholder="Damage, audit, correction..." required></div>
        </div>
        <button class="btn btn-warning" type="submit">Adjust Stock</button>
    </form>
</div>

<div class="card">
    <h3 style="margin-top:0;">Inventory List</h3>
    <div class="table-wrap">
        <table class="table" id="inventory-table">
            <thead>
            <tr>
                <th>SKU</th>
                <th>Product</th>
                <th>QOH</th>
                <th>Reserved</th>
                <th>Available</th>
                <th>Reorder Level</th>
                <th>Status</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($items as $item): ?>
                <?php
                $qoh = (int) $item['quantity_on_hand'];
                $reorder = (int) $item['reorder_level'];
                $statusClass = $qoh <= 0 ? 'status-out' : ($qoh <= $reorder ? 'status-low' : 'status-ok');
                $statusText = $qoh <= 0 ? 'Out' : ($qoh <= $reorder ? 'Low' : 'Healthy');
                ?>
                <tr>
                    <td><?= e($item['sku']) ?></td>
                    <td><?= e($item['product_name']) ?></td>
                    <td><?= (int) $item['quantity_on_hand'] ?></td>
                    <td><?= (int) $item['quantity_reserved'] ?></td>
                    <td><?= (int) $item['quantity_available'] ?></td>
                    <td><?= (int) $item['reorder_level'] ?></td>
                    <td><span class="status <?= $statusClass ?>"><?= $statusText ?></span></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
