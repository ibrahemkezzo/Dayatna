<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\RBAC;

use App\Http\Controllers\Controller;
use App\Http\Requests\RBAC\AssignRoleRequest;
use App\Http\Requests\RBAC\StorePermissionRequest;
use App\Http\Requests\RBAC\StoreRoleRequest;
use App\Http\Requests\RBAC\SyncPermissionsRequest;
use App\Http\Requests\RBAC\UpdatePermissionRequest;
use App\Http\Requests\RBAC\UpdateRoleRequest;
use App\Services\RBAC\RolePermissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RolePermissionController extends Controller
{
    public function __construct(
        protected RolePermissionService $rbacService
    ) {}

    // --- Role Endpoints ---

    /**
     * List Roles
     * Fetch all registered roles with their attached permissions.
     */
    public function indexRoles(): JsonResponse
    {
        return response()->json(['message' => 'Roles retrieved.', 'data' => $this->rbacService->getAllRoles()], Response::HTTP_OK);
    }

    /**
     * Create Role
     * Add a brand new dynamic role to the system.
     */
    public function storeRole(StoreRoleRequest $request): JsonResponse
    {
        $role = $this->rbacService->createRole($request->validated()['name']);
        return response()->json(['message' => 'Role created successfully.', 'data' => $role], Response::HTTP_CREATED);
    }

    /**
     * Update Role
     * Modify the designation name of a specific role.
     * @urlParam id integer required The role ID. Example: 4
     */
    public function updateRole(UpdateRoleRequest $request, int $id): JsonResponse
    {
        $role = $this->rbacService->updateRole($id, $request->validated()['name']);
        return response()->json(['message' => 'Role updated successfully.', 'data' => $role], Response::HTTP_OK);
    }

    /**
     * Delete Role
     * Wipe a role from the database completely. Cascades to permission relations.
     * @urlParam id integer required The role ID. Example: 4
     */
    public function destroyRole(int $id): JsonResponse
    {
        $this->rbacService->deleteRole($id);
        return response()->json(['message' => 'Role deleted successfully.'], Response::HTTP_OK);
    }

    // --- Permission Endpoints ---

    /**
     * List Permissions
     * Fetch a simple list of all native system system permissions.
     */
    public function indexPermissions(): JsonResponse
    {
        return response()->json(['message' => 'Permissions retrieved.', 'data' => $this->rbacService->getAllPermissions()], Response::HTTP_OK);
    }

    /**
     * Create Permission
     * Inject a new dynamic permission rule into the architecture.
     */
    public function storePermission(StorePermissionRequest $request): JsonResponse
    {
        $permission = $this->rbacService->createPermission($request->validated()['name']);
        return response()->json(['message' => 'Permission created successfully.', 'data' => $permission], Response::HTTP_CREATED);
    }

    /**
     * Update Permission
     * Modify the rule naming of an existing permission.
     * @urlParam id integer required The permission ID. Example: 12
     */
    public function updatePermission(UpdatePermissionRequest $request, int $id): JsonResponse
    {
        $permission = $this->rbacService->updatePermission($id, $request->validated()['name']);
        return response()->json(['message' => 'Permission updated successfully.', 'data' => $permission], Response::HTTP_OK);
    }

    /**
     * Delete Permission
     * Remove a permission permanently from the system.
     * @urlParam id integer required The permission ID. Example: 12
     */
    public function destroyPermission(int $id): JsonResponse
    {
        $this->rbacService->deletePermission($id);
        return response()->json(['message' => 'Permission deleted successfully.'], Response::HTTP_OK);
    }

    // --- Assignment Endpoints ---

    /**
     * Sync Role Permissions
     * Overwrite permissions tied to a specific role.
     * @urlParam id integer required The role ID. Example: 2
     */
    public function syncPermissions(SyncPermissionsRequest $request, int $id): JsonResponse
    {
        $role = $this->rbacService->syncPermissionsToRole($id, $request->validated()['permissions']);
        return response()->json(['message' => 'Permissions synced with role.', 'data' => $role], Response::HTTP_OK);
    }

    /**
     * Assign Role to User
     * Attach a core access role to a specific application user account.
     */
    public function assignRole(AssignRoleRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = $this->rbacService->assignRoleToUser((int)$data['user_id'], $data['role']);
        return response()->json([
            'message' => "Role '{$data['role']}' assigned to user.",
            'data'    => ['user_id' => $user->id, 'name' => $user->name, 'role' => $user->role]
        ], Response::HTTP_OK);
    }
}
