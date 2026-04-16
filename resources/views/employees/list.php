<div class="toolbar">
    <form method="get" action="<?= e(url('/employees')) ?>" class="flex flex-gap">
        <input class="input" type="text" name="search" value="<?= e($search) ?>" placeholder="Search employees...">
        <button class="btn btn-secondary" type="submit">Search</button>
    </form>
</div>

<div class="card">
    <h3 style="margin-top:0;">Add Employee</h3>
    <form method="post" action="<?= e(url('/employees')) ?>">
        <?= csrf_field() ?>
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
        <button class="btn btn-primary" type="submit">Create Employee</button>
    </form>
</div>

<div class="card">
    <h3 style="margin-top:0;">Employee List</h3>
    <div class="table-wrap">
        <table class="table">
            <thead>
            <tr>
                <th>Employee ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Department</th>
                <th>Position</th>
                <th>Role</th>
                <th>Status</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($employees as $employee): ?>
                <tr>
                    <td><?= e($employee['employee_id']) ?></td>
                    <td><?= e($employee['first_name'] . ' ' . $employee['last_name']) ?></td>
                    <td><?= e($employee['email']) ?></td>
                    <td><?= e($employee['department']) ?></td>
                    <td><?= e($employee['position']) ?></td>
                    <td><?= e($employee['role_name']) ?></td>
                    <td>
                        <span class="status <?= (int) $employee['is_active'] === 1 ? 'status-ok' : 'status-out' ?>">
                            <?= (int) $employee['is_active'] === 1 ? 'Active' : 'Inactive' ?>
                        </span>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
