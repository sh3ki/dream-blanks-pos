<?php
$canCreateEmployee = has_any_permission(['employees.create', 'users.create']);
$canUpdateEmployee = has_any_permission(['employees.update', 'users.update']);

$roleOptions = [];
foreach ($roles as $role) {
    $roleOptions[] = [
        'value' => (string) $role['role_name'],
        'label' => (string) $role['role_name'],
    ];
}

$filterConfig = [
    'targetTableId' => 'employees-table',
    'searchPlaceholder' => 'Search employees by ID, name, email, or department...',
    'searchColumns' => [0, 1, 2, 3, 4],
    'filterLabel' => 'Role',
    'filterColumn' => 5,
    'filterOptions' => $roleOptions,
    'dateColumn' => '',
    'emptyMessage' => 'No employees match your filters.',
    'enableClientPaging' => false,
];
?>

<div class="page-header">
    <div>
        <h2 class="page-title">Employees</h2>
        <p class="page-subtitle">Manage staff profiles, roles, and account status.</p>
    </div>
    <div class="page-actions">
        <?php if ($canCreateEmployee): ?>
            <button class="btn btn-primary" type="button" data-modal-open="employee-create-modal">Add Employee</button>
        <?php endif; ?>
    </div>
</div>

<?php require VIEW_PATH . '/components/list_filters.php'; ?>

<?php if ($canCreateEmployee): ?>
<div class="modal" id="employee-create-modal">
    <div class="modal-card modal-lg">
        <div class="modal-header">
            <strong>Add Employee</strong>
            <button class="modal-close" type="button" data-modal-close aria-label="Close">&times;</button>
        </div>
        <div class="modal-body">
            <form method="post" action="<?= e(url('/employees')) ?>" id="employee-create-form" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="employee-avatar" data-avatar-scope>
                    <div class="avatar avatar-lg" data-avatar-preview>NA</div>
                    <div class="field">
                        <label>Profile Photo</label>
                        <input class="input" type="file" name="avatar" accept="image/*" data-avatar-input>
                        <div class="help-text">Optional. PNG or JPG up to 2MB.</div>
                    </div>
                </div>
                <div class="grid grid-3">
                    <div class="field"><label>First Name</label><input class="input" name="first_name" required></div>
                    <div class="field"><label>Last Name</label><input class="input" name="last_name" required></div>
                    <div class="field"><label>Email</label><input class="input" type="email" name="email" required></div>
                    <div class="field"><label>Username</label><input class="input" name="username" required></div>
                    <div class="field"><label>Password</label><input class="input" type="password" name="password" required></div>
                    <div class="field">
                        <label>Role</label>
                        <select class="select" name="role_id" required>
                            <option value="">Select role</option>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?= (int) $role['id'] ?>"><?= e($role['role_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field"><label>Phone</label><input class="input" name="phone"></div>
                    <div class="field"><label>Department</label><input class="input" name="department"></div>
                    <div class="field"><label>Position</label><input class="input" name="position"></div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-ghost" type="button" data-modal-close>Cancel</button>
            <button class="btn btn-primary" type="submit" form="employee-create-form">Create Employee</button>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="card">
    <h3 class="card-title">Employee List</h3>
    <div class="table-wrap">
        <table class="table" id="employees-table" data-table>
            <thead>
            <tr>
                <th>Employee</th>
                <th>Employee ID</th>
                <th>Email</th>
                <th>Department</th>
                <th>Position</th>
                <th>Role</th>
                <th>Status</th>
                <th data-no-sort>Action</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($employees as $employee): ?>
                <?php
                $initials = strtoupper(substr($employee['first_name'] ?? '', 0, 1) . substr($employee['last_name'] ?? '', 0, 1));
                ?>
                <tr>
                    <td>
                        <div class="flex align-center flex-gap">
                            <div class="avatar avatar-sm"><?= e($initials !== '' ? $initials : 'NA') ?></div>
                            <div>
                                <strong><?= e($employee['first_name'] . ' ' . $employee['last_name']) ?></strong>
                            </div>
                        </div>
                    </td>
                    <td><?= e($employee['employee_id']) ?></td>
                    <td><?= e($employee['email']) ?></td>
                    <td><?= e($employee['department']) ?></td>
                    <td><?= e($employee['position']) ?></td>
                    <td><?= e($employee['role_name']) ?></td>
                    <td>
                        <span class="status <?= (int) $employee['is_active'] === 1 ? 'status-ok' : 'status-out' ?>">
                            <?= (int) $employee['is_active'] === 1 ? 'Active' : 'Inactive' ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($canUpdateEmployee): ?>
                            <div class="action-menu" data-menu>
                                <button class="btn btn-ghost btn-sm" type="button" data-menu-toggle>Actions</button>
                                <div class="menu" data-menu-list>
                                    <form method="post" action="<?= e(url('/employees/' . (int) $employee['id'] . '/status')) ?>">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="status" value="<?= (int) $employee['is_active'] === 1 ? 'inactive' : 'active' ?>">
                                        <button type="submit">
                                            <?= (int) $employee['is_active'] === 1 ? 'Deactivate' : 'Activate' ?>
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
                <a class="btn btn-ghost btn-sm <?= (int) ($page ?? 1) <= 1 ? 'hidden' : '' ?>" href="<?= e(url('/employees?page=1' . $querySuffix)) ?>">First</a>
                <a class="btn btn-ghost btn-sm <?= (int) ($page ?? 1) <= 1 ? 'hidden' : '' ?>" href="<?= e(url('/employees?page=' . $prevPage . $querySuffix)) ?>">Prev</a>
                <form class="page-jump" method="get" action="<?= e(url('/employees')) ?>">
                    <input class="input input-sm" type="number" min="1" max="<?= (int) ($totalPages ?? 1) ?>" name="page" value="<?= (int) ($page ?? 1) ?>">
                    <span class="page-total">of <?= (int) ($totalPages ?? 1) ?></span>
                    <?php if ($search !== ''): ?>
                        <input type="hidden" name="search" value="<?= e($search) ?>">
                    <?php endif; ?>
                </form>
                <a class="btn btn-ghost btn-sm <?= (int) ($page ?? 1) >= (int) ($totalPages ?? 1) ? 'hidden' : '' ?>" href="<?= e(url('/employees?page=' . $nextPage . $querySuffix)) ?>">Next</a>
                <a class="btn btn-ghost btn-sm <?= (int) ($page ?? 1) >= (int) ($totalPages ?? 1) ? 'hidden' : '' ?>" href="<?= e(url('/employees?page=' . $lastPage . $querySuffix)) ?>">Last</a>
            </div>
        </div>
    <?php endif; ?>
</div>
