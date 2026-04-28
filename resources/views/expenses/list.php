<?php
$canCreateExpense = has_any_permission(['expenses.create', 'expenses.manage']);
$canApproveExpense = has_any_permission(['expenses.approve', 'expenses.manage']);

$filterConfig = [
    'targetTableId' => 'expenses-table',
    'searchPlaceholder' => 'Search expenses by category or description...',
    'searchColumns' => [1, 2],
    'filterLabel' => 'Status',
    'filterColumn' => 4,
    'filterOptions' => [
        ['value' => 'Pending', 'label' => 'Pending'],
        ['value' => 'Approved', 'label' => 'Approved'],
        ['value' => 'Rejected', 'label' => 'Rejected'],
        ['value' => 'Paid', 'label' => 'Paid'],
    ],
    'dateColumn' => 0,
    'emptyMessage' => 'No expenses match your filters.',
];
?>

<div class="page-header">
    <div>
        <h2 class="page-title">Expenses</h2>
        <p class="page-subtitle">Record, approve, and track operational spending.</p>
    </div>
    <div class="page-actions">
        <?php if ($canCreateExpense): ?>
            <button class="btn btn-primary" type="button" data-modal-open="expense-create-modal">Record Expense</button>
        <?php endif; ?>
    </div>
</div>

<?php require VIEW_PATH . '/components/list_filters.php'; ?>

<?php if ($canCreateExpense): ?>
<div class="modal" id="expense-create-modal">
    <div class="modal-card modal-lg">
        <div class="modal-header">
            <strong>Record Expense</strong>
            <button class="modal-close" type="button" data-modal-close aria-label="Close">&times;</button>
        </div>
        <div class="modal-body">
            <form method="post" action="<?= e(url('/expenses')) ?>" id="expense-create-form">
                <?= csrf_field() ?>
                <div class="grid grid-3">
                    <div class="field">
                        <label>Category</label>
                        <select class="select" name="expense_category_id" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= (int) $category['id'] ?>"><?= e($category['category_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field"><label>Description</label><input class="input" name="description" required></div>
                    <div class="field"><label>Amount</label><input class="input" type="number" step="0.01" name="amount" required></div>
                    <div class="field"><label>Payment Method</label><select class="select" name="payment_method"><option>Cash</option><option>Check</option><option>Bank Transfer</option><option>Credit Card</option></select></div>
                    <div class="field"><label>Reference #</label><input class="input" name="reference_number"></div>
                    <div class="field"><label>Expense Date</label><input class="input" type="date" name="expense_date" value="<?= date('Y-m-d') ?>"></div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-ghost" type="button" data-modal-close>Cancel</button>
            <button class="btn btn-primary" type="submit" form="expense-create-form">Save Expense</button>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="card">
    <h3 class="card-title">Expense List</h3>
    <div class="table-wrap">
        <table class="table" id="expenses-table" data-table>
            <thead>
            <tr>
                <th>Date</th>
                <th>Category</th>
                <th>Description</th>
                <th>Amount</th>
                <th>Status</th>
                <th data-no-sort>Action</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($expenses as $expense): ?>
                <?php
                $statusClass = $expense['status'] === 'Approved' || $expense['status'] === 'Paid'
                    ? 'status-ok'
                    : ($expense['status'] === 'Rejected' ? 'status-out' : 'status-low');
                ?>
                <tr>
                    <td><?= e($expense['expense_date']) ?></td>
                    <td><?= e($expense['category_name']) ?></td>
                    <td><?= e($expense['description']) ?></td>
                    <td><?= number_format((float) $expense['amount'], 2) ?></td>
                    <td><span class="status <?= $statusClass ?>"><?= e($expense['status']) ?></span></td>
                    <td>
                        <?php if ($canApproveExpense && $expense['status'] === 'Pending'): ?>
                            <div class="action-menu" data-menu>
                                <button class="btn btn-ghost btn-sm" type="button" data-menu-toggle>Actions</button>
                                <div class="menu" data-menu-list>
                                    <form method="post" action="<?= e(url('/expenses/' . (int) $expense['id'] . '/approve')) ?>">
                                        <?= csrf_field() ?>
                                        <button type="submit">Approve</button>
                                    </form>
                                    <form method="post" action="<?= e(url('/expenses/' . (int) $expense['id'] . '/reject')) ?>" data-confirm="Reject this expense?">
                                        <?= csrf_field() ?>
                                        <button type="submit">Reject</button>
                                    </form>
                                </div>
                            </div>
                        <?php elseif ($canApproveExpense && $expense['status'] === 'Approved'): ?>
                            <div class="action-menu" data-menu>
                                <button class="btn btn-ghost btn-sm" type="button" data-menu-toggle>Actions</button>
                                <div class="menu" data-menu-list>
                                    <form method="post" action="<?= e(url('/expenses/' . (int) $expense['id'] . '/paid')) ?>">
                                        <?= csrf_field() ?>
                                        <button type="submit">Mark Paid</button>
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

    <div class="table-pagination" data-table-pagination data-target-table="expenses-table">
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
