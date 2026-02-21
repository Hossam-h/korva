<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define permissions
        $permissions = [
            // Admin management
            'create-admin',
            'view-admin',
            'update-admin',
            'delete-admin',

            // Academy management
            'view-academy',
            'update-academy',
            'change-academy-status',

            // Player management
            'view-player',
            'update-player',
            'delete-player',

            // Role & Permission management
            'create-role',
            'view-role',
            'update-role',
            'delete-role',
            'assign-role',
            'create-permission',
            'view-permission',
        ];

        // Create permissions for admin guard
        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name'       => $permission,
                'guard_name' => 'admin',
            ]);
        }

        // Create roles and assign permissions
        $superAdmin = Role::firstOrCreate([
            'name'       => 'super-admin',
            'guard_name' => 'admin',
        ]);
        // Super admin gets all permissions
        $superAdmin->syncPermissions(Permission::where('guard_name', 'admin')->get());

        $manager = Role::firstOrCreate([
            'name'       => 'manager',
            'guard_name' => 'admin',
        ]);
        
        $manager->syncPermissions([
            'view-admin',
            'view-academy',
            'update-academy',
            'change-academy-status',
            'view-player',
            'view-role',
            'view-permission',
        ]);
    }
}
