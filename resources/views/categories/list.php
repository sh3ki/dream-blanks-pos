<?php
$canCreateCategory = has_any_permission(['categories.create', 'products.manage']);
$canUpdateCategory = has_any_permission(['categories.update', 'products.manage']);
$canDeleteCategory = has_any_permission(['categories.delete', 'products.manage']);
$editCategory = $editCategory ?? null;

$filterConfig = [
    'targetTableId' => 'categories-table',
    'searchPlaceholder' => 'Search categories by name or parent...',
    'searchColumns' => [0, 1],
    'filterLabel' => 'Status',
    'filterColumn' => 3,
    'filterOptions' => [
        ['value' => 'Active', 'label' => 'Active'],
        ['value' => 'Inactive', 'label' => 'Inactive'],
    ],
    'dateColumn' => '',
    'emptyMessage' => 'No categories match your filters.',
];
?>

<div class="page-header">
    <div>
        <h2 class="page-title">Categories</h2>
        <p class="page-subtitle">Organize products into structured groups and tax rules.</p>
    </div>
    <div class="page-actions">
        <?php if ($canCreateCategory): ?>
            <button class="btn btn-primary" type="button" data-modal-open="category-create-modal">Add Category</button>
        <?php endif; ?>
    </div>
</div>

<?php require VIEW_PATH . '/components/list_filters.php'; ?>

<?php if ($canCreateCategory): ?>
<div class="modal" id="category-create-modal">
    <div class="modal-card">
        <div class="modal-header">
            <strong>Create Category</strong>
            <button class="modal-close" type="button" data-modal-close aria-label="Close">&times;</button>
        </div>
        <div class="modal-body">
            <form method="post" action="<?= e(url('/categories')) ?>" id="category-create-form">
                <?= csrf_field() ?>
                <div class="grid grid-3">
                    <div class="field"><label>Category Name</label><input class="input" name="category_name" required></div>
                    <div class="field"><label>Description</label><input class="input" name="description"></div>
                    <div class="field"><label>Parent Category ID</label><input class="input" type="number" name="parent_category_id"></div>
                    <div class="field"><label>Display Order</label><input class="input" type="number" name="display_order" value="0"></div>
                    <div class="field"><label>Tax Rate (%)</label><input class="input" type="number" step="0.01" name="tax_rate" value="0"></div>
                    <div class="field field-inline">
                        <label><input type="checkbox" name="is_active" value="1" checked> Active</label>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-ghost" type="button" data-modal-close>Cancel</button>
            <button class="btn btn-primary" type="submit" form="category-create-form">Save Category</button>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($canUpdateCategory && is_array($editCategory)): ?>
<div class="modal" id="category-edit-modal" data-modal-autoshow>
    <div class="modal-card">
        <div class="modal-header">
            <strong>Edit Category</strong>
            <button class="modal-close" type="button" data-modal-close aria-label="Close">&times;</button>
        </div>
        <div class="modal-body">
            <form method="post" action="<?= e(url('/categories/' . (int) $editCategory['id'] . '/update')) ?>" id="category-edit-form">
                <?= csrf_field() ?>
                <div class="grid grid-3">
                    <div class="field"><label>Category Name</label><input class="input" name="category_name" value="<?= e($editCategory['category_name']) ?>" required></div>
                    <div class="field"><label>Description</label><input class="input" name="description" value="<?= e($editCategory['description']) ?>"></div>
                    <div class="field"><label>Parent Category</label>
                        <select class="select" name="parent_category_id">
                            <option value="">None</option>
                            <?php foreach ($categories as $categoryOption): ?>
                                <?php if ((int) $categoryOption['id'] === (int) $editCategory['id']) { continue; } ?>
                                <option value="<?= (int) $categoryOption['id'] ?>" <?= (int) ($editCategory['parent_category_id'] ?? 0) === (int) $categoryOption['id'] ? 'selected' : '' ?>>
                                    <?= e($categoryOption['category_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field"><label>Display Order</label><input class="input" type="number" name="display_order" value="<?= (int) ($editCategory['display_order'] ?? 0) ?>"></div>
                    <div class="field"><label>Tax Rate (%)</label><input class="input" type="number" step="0.01" name="tax_rate" value="<?= e((string) ($editCategory['tax_rate'] ?? '0')) ?>"></div>
                    <div class="field field-inline">
                        <label><input type="checkbox" name="is_active" value="1" <?= (int) ($editCategory['is_active'] ?? 0) === 1 ? 'checked' : '' ?>> Active</label>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <a class="btn btn-ghost" href="<?= e(url('/categories')) ?>">Cancel</a>
            <button class="btn btn-primary" type="submit" form="category-edit-form">Update Category</button>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="card">
    <h3 class="card-title">Category List</h3>
    <div class="table-wrap">
        <table class="table" id="categories-table" data-table>
            <thead>
            <tr>
                <th>Name</th>
                <th>Parent</th>
                <th>Tax Rate</th>
                <th>Status</th>
                <th data-no-sort>Action</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($categories as $category): ?>
                <tr>
                    <td><?= e($category['category_name']) ?></td>
                    <td><?= e($category['parent_name']) ?></td>
                    <td><?= number_format((float) $category['tax_rate'], 2) ?>%</td>
                    <td>
                        <span class="status <?= (int) $category['is_active'] === 1 ? 'status-ok' : 'status-out' ?>">
                            <?= (int) $category['is_active'] === 1 ? 'Active' : 'Inactive' ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($canUpdateCategory): ?>
                            <div class="action-menu" data-menu>
                                <button class="btn btn-ghost btn-sm" type="button" data-menu-toggle>Actions</button>
                                <div class="menu" data-menu-list>
                                    <a href="<?= e(url('/categories?edit_id=' . (int) $category['id'])) ?>">Edit</a>
                                    <?php if ($canDeleteCategory): ?>
                                        <form method="post" action="<?= e(url('/categories/' . (int) $category['id'] . '/delete')) ?>" data-confirm="Delete this category?">
                                            <?= csrf_field() ?>
                                            <button type="submit">Delete</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php elseif ($canDeleteCategory): ?>
                            <form method="post" action="<?= e(url('/categories/' . (int) $category['id'] . '/delete')) ?>" data-confirm="Delete this category?">
                                <?= csrf_field() ?>
                                <button class="btn btn-danger btn-sm" type="submit">Delete</button>
                            </form>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="table-pagination" data-table-pagination data-target-table="categories-table">
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
