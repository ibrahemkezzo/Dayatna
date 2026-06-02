<?php

declare(strict_types=1);

namespace App\Services\RBAC;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserManagementService
{
    /**
     * Get paginated users with their assigned roles and permissions.
     */
    public function getPaginatedUsers(int $perPage = 15): LengthAwarePaginator
    {
        return User::query()
            ->with(['roles.permissions', 'permissions'])
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Create a brand new user directly from the admin dashboard management.
     */
    public function createUser(array $data): User
    {
        return DB::transaction(function () use ($data) {
            // Hash password configuration
            $data['password'] = Hash::make($data['password']);

            // Set default toggle state attributes if not dispatched
            $data['is_active'] = $data['is_active'] ?? true;
            $data['is_searchable'] = $data['is_searchable'] ?? true;

            $user = User::create([
                'name'          => $data['name'],
                'email'         => $data['email'],
                'phone'         => $data['phone'],
                'password'      => $data['password'],
                'role'          => $data['role'], // Keeping database enum in sync
                'is_active'     => $data['is_active'],
                'is_searchable' => $data['is_searchable'],
            ]);

            // Assign Spatie Role structural mapping
            $user->assignRole($data['role']);

            // Attach implicit direct permissions if provided in the payload
            if (!empty($data['permissions'])) {
                $user->givePermissionTo($data['permissions']);
            }

            return $user->load(['roles.permissions', 'permissions']);
        });
    }

    /**
     * Fetch a single user profile loaded with roles and custom permissions.
     */
    public function getUserById(int $id): User
    {
        return User::with(['roles.permissions', 'permissions', 'contacts'])->findOrFail($id);
    }

    /**
     * Update an existing user's data and access structures.
     */
    public function updateUser(int $id, array $data): User
    {
        return DB::transaction(function () use ($id, $data) {
            $user = User::findOrFail($id);

            // Hash password dynamically if dispatched in the request payload
            if (!empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            } else {
                unset($data['password']);
            }

            // Sync database column mapping if role is provided
            if (isset($data['role'])) {
                $user->syncRoles([$data['role']]);
            }

            // Sync explicit/direct permissions if provided
            if (isset($data['permissions'])) {
                $user->syncPermissions($data['permissions']);
            }

            $user->update($data);

            return $user->load(['roles.permissions', 'permissions']);
        });
    }

    /**
     * Remove a user account permanently from the architecture.
     */
    public function deleteUser(int $id): void
    {
        DB::transaction(function () use ($id) {
            $user = User::findOrFail($id);

            // Remove associated polymorphic contacts first
            $user->contacts()->delete();

            // Spatie handles role/permission relations detachment upon model deletion automatically
            $user->delete();
        });
    }
}
