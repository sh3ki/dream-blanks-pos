<?php
$canManageRoles = has_permission('roles.manage');
$canManagePermissions = has_permission('permissions.manage');
$canAssignUsers = has_permission('users.update');
?>

<div class="page-header">
    <div>
        <h2 class="page-title">Access Control</h2>
        <p class="page-subtitle">Create roles, manage permissions, and assign access.</p>
    </div>
    <div class="page-actions">
        <?php if ($canManageRoles): ?>
            <button class="btn btn-primary" type="button" data-modal-open="role-create-modal">New Role</button>
        <?php endif; ?>
        <?php if ($canManagePermissions): ?>
            <button class="btn btn-ghost" type="button" data-modal-open="permission-create-modal">New Permission</button>
        <?php endif; ?>
    </div>
</div>

<?php if ($canManageRoles): ?>
<div class="modal" id="role-create-modal">
    <div class="modal-card">
        <div class="modal-header">
            <strong>Create Role</strong>
            <button class="modal-close" type="button" data-modal-close aria-label="Close">&times;</button>
        </div>
        <div class="modal-body">
            <form method="post" action="<?= e(url('/access-control/roles')) ?>" id="role-create-form">
                <?= csrf_field() ?>
                <div class="grid grid-3">
                    <div class="field"><label>Role Name</label><input class="input" name="role_name" required></div>
                    <div class="field field-span-2"><label>Description</label><input class="input" name="description"></div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-ghost" type="button" data-modal-close>Cancel</button>
            <button class="btn btn-primary" type="submit" form="role-create-form">Add Role</button>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($canManagePermissions): ?>
<div class="modal" id="permission-create-modal">
    <div class="modal-card">
        <div class="modal-header">
            <strong>Create Permission</strong>
            <button class="modal-close" type="button" data-modal-close aria-label="Close">&times;</button>
        </div>
        <div class="modal-body">
            <form method="post" action="<?= e(url('/access-control/permissions')) ?>" id="permission-create-form">
                <?= csrf_field() ?>
                <div class="grid grid-3">
                    <div class="field"><label>Permission Code</label><input class="input" name="permission_name" placeholder="products.update" required></div>
                    <div class="field"><label>Module</label><input class="input" name="module" placeholder="products"></div>
                    <div class="field"><label>Description</label><input class="input" name="description"></div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-ghost" type="button" data-modal-close>Cancel</button>
            <button class="btn btn-primary" type="submit" form="permission-create-form">Add Permission</button>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($canManageRoles || $canManagePermissions): ?>
<div class="card">
    <h3 class="card-title">Role Permission Matrix</h3>
    <?php foreach ($roles as $role): ?>
        <?php
        $roleId = (int) $role['id'];
        $rolePermissionIds = $rolePermissionMap[$roleId] ?? [];
        ?>
        <form method="post" action="<?= e(url('/access-control/roles/permissions')) ?>" class="permission-panel">
            <?= csrf_field() ?>
            <input type="hidden" name="role_id" value="<?= $roleId ?>">

            <div class="permission-header">
                <div>
                    <strong><?= e($role['role_name']) ?></strong>
                    <div class="card-subtitle"><?= e($role['description']) ?></div>
                </div>
                <?php if ($canManageRoles || $canManagePermissions): ?>
                    <button class="btn btn-secondary" type="submit">Save Permissions</button>
                <?php endif; ?>
            </div>

            <?php foreach ($permissionsByModule as $module => $modulePermissions): ?>
                <div class="permission-module-title"><?= e($module) ?></div>
                <div class="grid grid-3 permission-grid">
                    <?php foreach ($modulePermissions as $permission): ?>
                        <?php $permissionId = (int) $permission['id']; ?>
                        <label class="permission-item">
                            <input
                                type="checkbox"
                                name="permission_ids[]"
                                value="<?= $permissionId ?>"
                                <?= in_array($permissionId, $rolePermissionIds, true) ? 'checked' : '' ?>
                                <?= ($canManageRoles || $canManagePermissions) ? '' : 'disabled' ?>
                            >
                            <span class="permission-label"><?= e($permission['permission_name']) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </form>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php if ($canAssignUsers): ?>
<div class="card">
    <h3 class="card-title">Assign User Role</h3>
    <div class="table-wrap">
        <table class="table" data-table id="access-users-table">
            <thead>
            <tr>
                <th>User</th>
                <th>Username</th>
                <th>Email</th>
                <th>Status</th>
                <th>Role</th>
                <th data-no-sort>Action</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= e(trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''))) ?></td>
                    <td><?= e($user['username']) ?></td>
                    <td><?= e($user['email']) ?></td>
                    <td>
                        <span class="status <?= (int) $user['is_active'] === 1 ? 'status-ok' : 'status-out' ?>">
                            <?= (int) $user['is_active'] === 1 ? 'Active' : 'Inactive' ?>
                        </span>
                    </td>
                    <td>
                        <form method="post" action="<?= e(url('/access-control/users/role')) ?>" class="flex flex-gap align-center">
                            <?= csrf_field() ?>
                            <input type="hidden" name="user_id" value="<?= (int) $user['id'] ?>">
                            <select class="select select-wide" name="role_id">
                                <?php foreach ($roles as $role): ?>
                                    <option value="<?= (int) $role['id'] ?>" <?= (int) $role['id'] === (int) $user['role_id'] ? 'selected' : '' ?>>
                                        <?= e($role['role_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                    </td>
                    <td>
                            <button class="btn btn-primary" type="submit">Update Role</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="table-pagination" data-table-pagination data-target-table="access-users-table">
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
<?php endif; ?>
