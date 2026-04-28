<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Services\AccessControlService;

class AccessControlController extends Controller
{
    public function index(Request $request): void
    {
        $this->authorizeAnyPermission(['roles.manage', 'permissions.manage', 'users.update']);

        $service = new AccessControlService();
        $roles = $service->roles();
        $permissions = $service->permissions();

        $permissionsByModule = [];
        foreach ($permissions as $permission) {
            $module = trim((string) ($permission['module'] ?? 'general'));
            if ($module === '') {
                $module = 'general';
            }

            if (!isset($permissionsByModule[$module])) {
                $permissionsByModule[$module] = [];
            }
            $permissionsByModule[$module][] = $permission;
        }

        ksort($permissionsByModule);

        $this->render('access_control.index', [
            'title' => 'Roles & Permissions',
            'roles' => $roles,
            'permissionsByModule' => $permissionsByModule,
            'rolePermissionMap' => $service->rolePermissionMap(),
            'users' => $service->users(),
            'flash' => consume_flash(),
        ]);
    }

    public function storeRole(Request $request): void
    {
        $this->authorizePermission('roles.manage');

        $name = trim((string) $request->input('role_name'));
        $description = trim((string) $request->input('description'));

        try {
            $service = new AccessControlService();
            $roleId = $service->createRole($name, $description === '' ? null : $description);
            log_activity('role.created', ['role_id' => $roleId, 'role_name' => $name]);
            flash('success', 'Role created successfully.');
        } catch (\Throwable $e) {
            flash('error', 'Unable to create role: ' . $e->getMessage());
        }

        $this->redirect('/access-control');
    }

    public function storePermission(Request $request): void
    {
        $this->authorizePermission('permissions.manage');

        $permissionName = trim((string) $request->input('permission_name'));
        $module = trim((string) $request->input('module'));
        $description = trim((string) $request->input('description'));

        try {
            $service = new AccessControlService();
            $permissionId = $service->createPermission(
                $permissionName,
                $module === '' ? null : $module,
                $description === '' ? null : $description
            );
            log_activity('permission.created', ['permission_id' => $permissionId, 'permission_name' => $permissionName]);
            flash('success', 'Permission created successfully.');
        } catch (\Throwable $e) {
            flash('error', 'Unable to create permission: ' . $e->getMessage());
        }

        $this->redirect('/access-control');
    }

    public function syncRolePermissions(Request $request): void
    {
        $this->authorizeAnyPermission(['roles.manage', 'permissions.manage']);

        $roleId = (int) $request->input('role_id');
        $permissionIds = $request->input('permission_ids', []);
        if (!is_array($permissionIds)) {
            $permissionIds = [];
        }

        try {
            $service = new AccessControlService();
            $service->syncRolePermissions($roleId, $permissionIds);
            log_activity('role.permissions.synced', ['role_id' => $roleId, 'permission_ids' => array_values($permissionIds)]);
            flash('success', 'Role permissions updated successfully.');
        } catch (\Throwable $e) {
            flash('error', 'Unable to update role permissions: ' . $e->getMessage());
        }

        $this->redirect('/access-control');
    }

    public function assignUserRole(Request $request): void
    {
        $this->authorizePermission('users.update');

        $userId = (int) $request->input('user_id');
        $roleId = (int) $request->input('role_id');

        try {
            $service = new AccessControlService();
            $service->assignUserRole($userId, $roleId);
            log_activity('user.role.updated', ['user_id' => $userId, 'role_id' => $roleId]);
            flash('success', 'User role updated successfully.');
        } catch (\Throwable $e) {
            flash('error', 'Unable to update user role: ' . $e->getMessage());
        }

        $this->redirect('/access-control');
    }
}
