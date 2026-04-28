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
?>

<div class="page-header">
    <div>
        <h2 class="page-title">Inventory</h2>
        <p class="page-subtitle">Track stock levels, reservations, and reorder health.</p>
    </div>
    <div class="page-actions">
        <button class="btn btn-warning" type="button" data-modal-open="inventory-adjust-modal">Adjust Stock</button>
    </div>
</div>

<?php require VIEW_PATH . '/components/list_filters.php'; ?>

<div class="modal" id="inventory-adjust-modal">
    <div class="modal-card">
        <div class="modal-header">
            <strong>Stock Adjustment</strong>
            <button class="modal-close" type="button" data-modal-close aria-label="Close">&times;</button>
        </div>
        <div class="modal-body">
            <form method="post" action="<?= e(url('/inventory/adjust')) ?>" id="inventory-adjust-form">
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
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-ghost" type="button" data-modal-close>Cancel</button>
            <button class="btn btn-warning" type="submit" form="inventory-adjust-form">Adjust Stock</button>
        </div>
    </div>
</div>

<div class="card">
    <h3 class="card-title">Inventory List</h3>
    <div class="table-wrap">
        <table class="table" id="inventory-table" data-table>
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

    <div class="table-pagination" data-table-pagination data-target-table="inventory-table">
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
