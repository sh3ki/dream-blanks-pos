<div class="card">
    <h3 style="margin-top:0;">Inventory Report</h3>
    <div class="table-wrap">
        <table class="table">
            <thead>
            <tr>
                <th>SKU</th>
                <th>Product</th>
                <th>QOH</th>
                <th>Reorder Level</th>
                <th>Stock Value</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td><?= e($row['sku']) ?></td>
                    <td><?= e($row['product_name']) ?></td>
                    <td><?= (int) $row['quantity_on_hand'] ?></td>
                    <td><?= (int) $row['reorder_level'] ?></td>
                    <td><?= number_format((float) $row['stock_value'], 2) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
