<div class="card">
    <h3 style="margin-top:0;">Create Category</h3>
    <form method="post" action="<?= e(url('/categories')) ?>">
        <?= csrf_field() ?>
        <div class="grid grid-3">
            <div class="field"><label>Category Name</label><input class="input" name="category_name" required></div>
            <div class="field"><label>Description</label><input class="input" name="description"></div>
            <div class="field"><label>Parent Category ID</label><input class="input" type="number" name="parent_category_id"></div>
            <div class="field"><label>Display Order</label><input class="input" type="number" name="display_order" value="0"></div>
            <div class="field"><label>Tax Rate (%)</label><input class="input" type="number" step="0.01" name="tax_rate" value="0"></div>
            <div class="field" style="display:flex;align-items:end;">
                <label><input type="checkbox" name="is_active" value="1" checked> Active</label>
            </div>
        </div>
        <button class="btn btn-primary" type="submit">Save Category</button>
    </form>
</div>

<div class="card">
    <h3 style="margin-top:0;">Category List</h3>
    <div class="table-wrap">
        <table class="table">
            <thead>
            <tr>
                <th>Name</th>
                <th>Parent</th>
                <th>Tax Rate</th>
                <th>Status</th>
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
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
