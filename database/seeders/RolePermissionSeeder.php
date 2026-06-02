<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Reset cached roles and permissions (Crucial for Spatie)
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Define the exact guard name used for APIs (usually 'sanctum' or 'api')
        $guardName = 'web'; // Change to 'api' or 'sanctum' depending on your config/auth.php

        // 3. System Analysis: Define all system permissions
        $permissions = [
            // RBAC Management
            'view_roles', 'create_roles', 'edit_roles', 'delete_roles',
            'view_users', 'create_users', 'edit_users', 'delete_users',

            // Categories Management
            'view_categories', 'create_categories', 'edit_categories', 'delete_categories',

            // Facilities Management (Stadiums, Shops, etc.)
            'view_facilities', 'create_facilities', 'edit_facilities', 'delete_facilities',

            // Offers Management
            'view_offers', 'create_offers', 'edit_offers', 'delete_offers',

            // Bookings Management
            'view_bookings', 'create_bookings', 'edit_bookings', 'delete_bookings', 'update_booking_status',

            // Reviews Management
            'create_reviews', 'delete_reviews',
        ];

        // 4. Insert permissions using Spatie Models
        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, $guardName);
        }

        // 5. Create Roles and Assign Permissions

        // A. Admin Role: Absolute power over the system
        $admin = Role::findOrCreate('admin', $guardName);
        $admin->givePermissionTo(Permission::all());

        // B. Owner Role: Facility owner (Stadium/Shop owner)
        $owner = Role::findOrCreate('owner', $guardName);
        $owner->givePermissionTo([
            'view_facilities', 'create_facilities', 'edit_facilities', 'delete_facilities',
            'view_offers', 'create_offers', 'edit_offers', 'delete_offers',
            'view_bookings', 'update_booking_status',
            // Note: Scoping to "only their own facilities/bookings" MUST be done via Laravel Policies.
        ]);

        // C. Customer Role: Normal citizen booking a stadium or viewing offers
        $customer = Role::findOrCreate('customer', $guardName);
        $customer->givePermissionTo([
            'view_facilities',
            'view_offers',
            'view_bookings', 'create_bookings',
            'create_reviews',
        ]);
    }
}

