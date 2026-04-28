<?php
$canCreateProduct = has_any_permission(['products.create', 'products.manage']);
$canUpdateProduct = has_any_permission(['products.update', 'products.manage']);
$editProduct = $editProduct ?? null;

$categoryOptions = [];
foreach ($categories as $category) {
    $categoryOptions[] = [
        'value' => (string) $category['category_name'],
        'label' => (string) $category['category_name'],
    ];
}

$filterConfig = [
    'targetTableId' => 'products-table',
    'searchPlaceholder' => 'Search products by SKU, name, or category...',
    'searchColumns' => [0, 1, 2],
    'filterLabel' => 'Category',
    'filterColumn' => 2,
    'filterOptions' => $categoryOptions,
    'dateColumn' => '',
    'emptyMessage' => 'No products match your filters.',
    'enableClientPaging' => false,
];
?>

<div class="page-header">
    <div>
        <h2 class="page-title">Products</h2>
        <p class="page-subtitle">Manage your catalog, pricing, and stock visibility.</p>
    </div>
    <div class="page-actions">
        <?php if ($canCreateProduct): ?>
            <button class="btn btn-primary" type="button" data-modal-open="product-create-modal">Add Product</button>
        <?php endif; ?>
    </div>
</div>

<?php require VIEW_PATH . '/components/list_filters.php'; ?>

<?php if ($canCreateProduct): ?>
<div class="modal" id="product-create-modal">
    <div class="modal-card modal-lg">
        <div class="modal-header">
            <strong>Add Product</strong>
            <button class="modal-close" type="button" data-modal-close aria-label="Close">&times;</button>
        </div>
        <div class="modal-body">
            <form method="post" action="<?= e(url('/products')) ?>" id="product-create-form" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="product-media" data-image-scope>
                    <div class="image-preview" data-image-preview>Product Image</div>
                    <div class="field">
                        <label>Image Upload</label>
                        <input class="input" type="file" name="product_image" accept="image/*" data-image-input>
                        <div class="help-text">Optional. PNG or JPG up to 2MB.</div>
                    </div>
                </div>
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
                    <div class="field field-inline"><label><input type="checkbox" name="is_active" value="1" checked> Active</label></div>
                </div>
                <div class="field">
                    <label>Description</label>
                    <textarea class="textarea" name="description" rows="3"></textarea>
                </div>
                <div class="field">
                    <label>Features (comma-separated)</label>
                    <input class="input" name="features" data-chip-input data-chip-preview="product-features-preview" placeholder="e.g. Cotton, Size M, Color Black">
                    <div class="chip-group" id="product-features-preview"></div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-ghost" type="button" data-modal-close>Cancel</button>
            <button class="btn btn-primary" type="submit" form="product-create-form">Save Product</button>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($canUpdateProduct && is_array($editProduct)): ?>
<div class="modal" id="product-edit-modal" data-modal-autoshow>
    <div class="modal-card modal-lg">
        <div class="modal-header">
            <strong>Edit Product</strong>
            <button class="modal-close" type="button" data-modal-close aria-label="Close">&times;</button>
        </div>
        <div class="modal-body">
            <form method="post" action="<?= e(url('/products/' . (int) $editProduct['id'] . '/update')) ?>" id="product-edit-form" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="product-media" data-image-scope>
                    <div class="image-preview" data-image-preview>Product Image</div>
                    <div class="field">
                        <label>Image Upload</label>
                        <input class="input" type="file" name="product_image" accept="image/*" data-image-input>
                        <div class="help-text">Optional. PNG or JPG up to 2MB.</div>
                    </div>
                </div>
                <div class="grid grid-3">
                    <div class="field"><label>SKU</label><input class="input" name="sku" value="<?= e($editProduct['sku']) ?>" required></div>
                    <div class="field"><label>Product Name</label><input class="input" name="product_name" value="<?= e($editProduct['product_name']) ?>" required></div>
                    <div class="field"><label>Category</label>
                        <select class="select" name="category_id">
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= (int) $category['id'] ?>" <?= (int) ($editProduct['category_id'] ?? 0) === (int) $category['id'] ? 'selected' : '' ?>>
                                    <?= e($category['category_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field"><label>Cost Price</label><input class="input" name="cost_price" type="number" step="0.01" value="<?= e((string) ($editProduct['cost_price'] ?? '0')) ?>"></div>
                    <div class="field"><label>Selling Price</label><input class="input" name="selling_price" type="number" step="0.01" value="<?= e((string) ($editProduct['selling_price'] ?? '0')) ?>" required></div>
                    <div class="field"><label>Wholesale Price</label><input class="input" name="wholesale_price" type="number" step="0.01" value="<?= e((string) ($editProduct['wholesale_price'] ?? '0')) ?>"></div>
                    <div class="field"><label>Reorder Level</label><input class="input" name="reorder_level" type="number" value="<?= (int) ($editProduct['reorder_level'] ?? 10) ?>"></div>
                    <div class="field"><label>Reorder Quantity</label><input class="input" name="reorder_quantity" type="number" value="<?= (int) ($editProduct['reorder_quantity'] ?? 20) ?>"></div>
                    <div class="field"><label>Unit</label><input class="input" name="unit_of_measurement" value="<?= e((string) ($editProduct['unit_of_measurement'] ?? 'Piece')) ?>"></div>
                    <div class="field"><label>Tax Treatment</label>
                        <select class="select" name="tax_treatment">
                            <option <?= ($editProduct['tax_treatment'] ?? '') === 'Taxable' ? 'selected' : '' ?>>Taxable</option>
                            <option <?= ($editProduct['tax_treatment'] ?? '') === 'Tax-exempt' ? 'selected' : '' ?>>Tax-exempt</option>
                        </select>
                    </div>
                    <div class="field field-inline"><label><input type="checkbox" name="is_active" value="1" <?= (int) ($editProduct['is_active'] ?? 0) === 1 ? 'checked' : '' ?>> Active</label></div>
                </div>
                <div class="field"><label>Description</label><textarea class="textarea" name="description" rows="3"><?= e((string) ($editProduct['description'] ?? '')) ?></textarea></div>
                <div class="field">
                    <label>Features (comma-separated)</label>
                    <input class="input" name="features" data-chip-input data-chip-preview="product-features-preview-edit" value="<?= e((string) ($editProduct['features'] ?? '')) ?>" placeholder="e.g. Cotton, Size M, Color Black">
                    <div class="chip-group" id="product-features-preview-edit"></div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <a class="btn btn-ghost" href="<?= e(url('/products')) ?>">Cancel</a>
            <button class="btn btn-primary" type="submit" form="product-edit-form">Update Product</button>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="card">
    <h3 class="card-title">Product List</h3>
    <div class="table-wrap">
        <table class="table" id="products-table" data-table>
            <thead>
            <tr>
                <th>SKU</th>
                <th>Name</th>
                <th>Category</th>
                <th>Selling Price</th>
                <th>Stock</th>
                <th>Features</th>
                <th>Status</th>
                <th>Record Status</th>
                <th data-no-sort>Action</th>
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
                    <td>
                        <?php
                        $features = array_filter(array_map('trim', explode(',', (string) ($product['features'] ?? ''))));
                        ?>
                        <?php if (count($features) > 0): ?>
                            <div class="chip-group">
                                <?php foreach (array_slice($features, 0, 3) as $feature): ?>
                                    <span class="chip"><?= e($feature) ?></span>
                                <?php endforeach; ?>
                                <?php if (count($features) > 3): ?>
                                    <span class="chip">+<?= count($features) - 3 ?></span>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                    <td><span class="status <?= $statusClass ?>"><?= $statusText ?></span></td>
                    <td>
                        <span class="status <?= (int) $product['is_active'] === 1 ? 'status-ok' : 'status-out' ?>">
                            <?= (int) $product['is_active'] === 1 ? 'Active' : 'Inactive' ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($canUpdateProduct): ?>
                            <div class="action-menu" data-menu>
                                <button class="btn btn-ghost btn-sm" type="button" data-menu-toggle>Actions</button>
                                <div class="menu" data-menu-list>
                                    <a href="<?= e(url('/products?edit_id=' . (int) $product['id'])) ?>">Edit</a>
                                    <form method="post" action="<?= e(url('/products/' . (int) $product['id'] . '/status')) ?>">
                                        <?= csrf_field() ?>
                                        <button type="submit">
                                            <?= (int) $product['is_active'] === 1 ? 'Deactivate' : 'Activate' ?>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if (($totalPages ?? 1) > 1): ?>
        <?php
        $querySuffix = $search !== '' ? '&search=' . urlencode($search) : '';
        $prevPage = max(1, ((int) ($page ?? 1)) - 1);
        $nextPage = min((int) ($totalPages ?? 1), ((int) ($page ?? 1)) + 1);
        $lastPage = (int) ($totalPages ?? 1);
        ?>
        <div class="table-pagination">
            <div>Page <?= (int) ($page ?? 1) ?> of <?= (int) ($totalPages ?? 1) ?> (<?= (int) ($total ?? 0) ?> records)</div>
            <div class="pagination">
                <a class="btn btn-ghost btn-sm <?= (int) ($page ?? 1) <= 1 ? 'hidden' : '' ?>" href="<?= e(url('/products?page=1' . $querySuffix)) ?>">First</a>
                <a class="btn btn-ghost btn-sm <?= (int) ($page ?? 1) <= 1 ? 'hidden' : '' ?>" href="<?= e(url('/products?page=' . $prevPage . $querySuffix)) ?>">Prev</a>
                <form class="page-jump" method="get" action="<?= e(url('/products')) ?>">
                    <input class="input input-sm" type="number" min="1" max="<?= (int) ($totalPages ?? 1) ?>" name="page" value="<?= (int) ($page ?? 1) ?>">
                    <span class="page-total">of <?= (int) ($totalPages ?? 1) ?></span>
                    <?php if ($search !== ''): ?>
                        <input type="hidden" name="search" value="<?= e($search) ?>">
                    <?php endif; ?>
                </form>
                <a class="btn btn-ghost btn-sm <?= (int) ($page ?? 1) >= (int) ($totalPages ?? 1) ? 'hidden' : '' ?>" href="<?= e(url('/products?page=' . $nextPage . $querySuffix)) ?>">Next</a>
                <a class="btn btn-ghost btn-sm <?= (int) ($page ?? 1) >= (int) ($totalPages ?? 1) ? 'hidden' : '' ?>" href="<?= e(url('/products?page=' . $lastPage . $querySuffix)) ?>">Last</a>
            </div>
        </div>
    <?php endif; ?>
</div>
