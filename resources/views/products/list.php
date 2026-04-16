<div class="toolbar">
    <form method="get" action="<?= e(url('/products')) ?>" class="flex flex-gap">
        <input class="input" type="text" name="search" value="<?= e($search) ?>" placeholder="Search by name or SKU...">
        <button class="btn btn-secondary" type="submit">Search</button>
    </form>
</div>

<div class="card">
    <h3 style="margin-top:0;">Add Product</h3>
    <form method="post" action="<?= e(url('/products')) ?>">
        <?= csrf_field() ?>
        <div class="grid grid-3">
            <div class="field"><label>SKU</label><input class="input" name="sku" required></div>
            <div class="field"><label>Product Name</label><input class="input" name="product_name" required></div>
            <div class="field"><label>Category</label>
                <select class="select" name="category_id">
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= (int) $category['id'] ?>"><?= e($category['category_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field"><label>Cost Price</label><input class="input" name="cost_price" type="number" step="0.01" value="0"></div>
            <div class="field"><label>Selling Price</label><input class="input" name="selling_price" type="number" step="0.01" required></div>
            <div class="field"><label>Wholesale Price</label><input class="input" name="wholesale_price" type="number" step="0.01" value="0"></div>
            <div class="field"><label>Initial Stock</label><input class="input" name="initial_stock" type="number" value="0"></div>
            <div class="field"><label>Reorder Level</label><input class="input" name="reorder_level" type="number" value="10"></div>
            <div class="field"><label>Reorder Quantity</label><input class="input" name="reorder_quantity" type="number" value="20"></div>
            <div class="field"><label>Unit</label><input class="input" name="unit_of_measurement" value="Piece"></div>
            <div class="field"><label>Tax Treatment</label><select class="select" name="tax_treatment"><option>Taxable</option><option>Tax-exempt</option></select></div>
            <div class="field" style="display:flex;align-items:end;"><label><input type="checkbox" name="is_active" value="1" checked> Active</label></div>
        </div>
        <div class="field"><label>Description</label><textarea class="textarea" name="description" rows="3"></textarea></div>
        <button class="btn btn-primary" type="submit">Save Product</button>
    </form>
</div>

<div class="card">
    <h3 style="margin-top:0;">Product List</h3>
    <div class="table-wrap">
        <table class="table">
            <thead>
            <tr>
                <th>SKU</th>
                <th>Name</th>
                <th>Category</th>
                <th>Selling Price</th>
                <th>Stock</th>
                <th>Status</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($products as $product): ?>
                <?php
                $qty = (int) $product['quantity_on_hand'];
                $reorder = (int) $product['reorder_level'];
                $statusClass = $qty <= 0 ? 'status-out' : ($qty <= $reorder ? 'status-low' : 'status-ok');
                $statusText = $qty <= 0 ? 'Out of Stock' : ($qty <= $reorder ? 'Low Stock' : 'In Stock');
                ?>
                <tr>
                    <td><?= e($product['sku']) ?></td>
                    <td><?= e($product['product_name']) ?></td>
                    <td><?= e($product['category_name']) ?></td>
                    <td><?= number_format((float) $product['selling_price'], 2) ?></td>
                    <td><?= (int) $product['quantity_on_hand'] ?></td>
                    <td><span class="status <?= $statusClass ?>"><?= $statusText ?></span></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
