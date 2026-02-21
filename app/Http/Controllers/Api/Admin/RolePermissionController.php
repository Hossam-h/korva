<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Admin\AssignPermissionsRequest;
use App\Http\Requests\Admin\AssignRoleRequest;
use App\Http\Requests\Admin\CreatePermissionRequest;
use App\Http\Requests\Admin\CreateRoleRequest;
use App\Models\Admin;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionController extends BaseController
{
    /**
     * Create a new role (optionally with permissions).
     * POST /admin/roles
     */
    public function createRole(CreateRoleRequest $request)
    {
        $role = Role::create([
            'name'       => $request->name,
            'guard_name' => 'admin',
        ]);

        if ($request->has('permissions')) {
            $role->givePermissionTo($request->permissions);
        }

        $role->load('permissions');

        return $this->sendResponse($role, 'Role created successfully');
    }

    /**
     * Create a new permission.
     * POST /admin/permissions
     */
    public function createPermission(CreatePermissionRequest $request)
    {
        $permission = Permission::create([
            'name'       => $request->name,
            'guard_name' => 'admin',
        ]);

        return $this->sendResponse($permission, 'Permission created successfully');
    }

    /**
     * List all permissions for admin guard.
     * GET /admin/permissions
     */
    public function allPermissions()
    {
        $permissions = Permission::where('guard_name', 'admin')->get();

        return $this->sendResponse($permissions, 'Permissions retrieved successfully');
    }

    /**
     * List all roles for admin guard.
     * GET /admin/roles
     */
    public function allRoles()
    {
        $roles = Role::where('guard_name', 'admin')->get();

        return $this->sendResponse($roles, 'Roles retrieved successfully');
    }

    /**
     * Get a single role with its permissions.
     * GET /admin/roles/{id}
     */
    public function roleWithPermissions($id)
    {
        $role = Role::where('guard_name', 'admin')->find($id);

        if (!$role) {
            return $this->sendError('Role not found', [], 404);
        }

        $role->load('permissions');

        return $this->sendResponse($role, 'Role retrieved successfully');
    }

    /**
     * Assign role(s) to an Admin.
     * POST /admin/admins/{admin}/assign-role
     */
    public function assignRole(AssignRoleRequest $request, Admin $admin)
    {
        $admin->assignRole($request->role);

        $admin->load('roles.permissions');

        return $this->sendResponse($admin, 'Roles assigned successfully');
    }

    /**
     * Remove role(s) from an Admin.
     * POST /admin/admins/{admin}/remove-role
     */
    public function removeRole(AssignRoleRequest $request, Admin $admin)
    {
        foreach ($request->roles as $role) {
            $admin->removeRole($role);
        }

        $admin->load('roles.permissions');

        return $this->sendResponse($admin, 'Roles removed successfully');
    }

    /**
     * Assign (sync) permissions to a role.
     * POST /admin/roles/{id}/assign-permissions
     */
    public function assignPermissionsToRole(AssignPermissionsRequest $request, $id)
    {
        $role = Role::where('guard_name', 'admin')->find($id);

        if (!$role) {
            return $this->sendError('Role not found', [], 404);
        }

        $role->syncPermissions($request->permissions);

        $role->load('permissions');

        return $this->sendResponse($role, 'Permissions assigned to role successfully');
    }

    /**
     * Remove permissions from a role.
     * POST /admin/roles/{id}/remove-permissions
     */
    public function removePermissionsFromRole(AssignPermissionsRequest $request, $id)
    {
        $role = Role::where('guard_name', 'admin')->find($id);

        if (!$role) {
            return $this->sendError('Role not found', [], 404);
        }

        foreach ($request->permissions as $permission) {
            $role->revokePermissionTo($permission);
        }

        $role->load('permissions');

        return $this->sendResponse($role, 'Permissions removed from role successfully');
    }
}
