<?php

declare(strict_types=1);

namespace App\Services\RBAC;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionService
{
    // ==========================================
    // ROLE CRUD OPERATIONS
    // ==========================================

    public function getAllRoles(): Collection
    {
        return Role::with('permissions')->get();
    }

    public function createRole(string $name): Role
    {
        return Role::create(['name' => $name, 'guard_name' => 'web']);
    }

    public function updateRole(int $id, string $name): Role
    {
        $role = Role::findOrFail($id);
        $role->update(['name' => $name]);
        return $role;
    }

    public function deleteRole(int $id): void
    {
        $role = Role::findOrFail($id);
        $role->delete(); // Spatie automatically cleans up the pivot tables
    }

    // ==========================================
    // PERMISSION CRUD OPERATIONS
    // ==========================================

    public function getAllPermissions(): Collection
    {
        return Permission::all();
    }

    public function createPermission(string $name): Permission
    {
        return Permission::create(['name' => $name, 'guard_name' => 'web']);
    }

    public function updatePermission(int $id, string $name): Permission
    {
        $permission = Permission::findOrFail($id);
        $permission->update(['name' => $name]);
        return $permission;
    }

    public function deletePermission(int $id): void
    {
        $permission = Permission::findOrFail($id);
        $permission->delete();
    }

    // ==========================================
    // ASSIGNMENTS & SYNCS
    // ==========================================

    public function syncPermissionsToRole(int $roleId, array $permissions): Role
    {
        $role = Role::findOrFail($roleId);
        $role->syncPermissions($permissions);
        return $role->load('permissions');
    }

    public function assignRoleToUser(int $userId, string $roleName): User
    {
        $user = User::findOrFail($userId);
        $user->syncRoles([$roleName]);
        $user->update(['role' => $roleName]); // Keeping database enum in sync
        return $user;
    }
}
