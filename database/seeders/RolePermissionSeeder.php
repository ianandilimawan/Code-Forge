<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Permission;
use App\Models\Menu;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        // Create Admin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@redtech.co.id'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('redtech.co.id'),
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Admin user created: admin@retech.co.id / redtech.co.id');

        // Create System Menus
        $menus = [
            [
                'name' => 'Dashboard',
                'route' => 'admin.dashboard',
                'slug' => 'dashboard',
                'icon' => 'home',
                'sort_order' => 0,
                'is_active' => true,
            ],
            [
                'name' => 'Users',
                'route' => 'admin.users.index',
                'slug' => 'users',
                'icon' => 'users',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Roles',
                'route' => 'admin.roles.index',
                'slug' => 'roles',
                'icon' => 'shield',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Permissions',
                'route' => 'admin.permissions.index',
                'slug' => 'permissions',
                'icon' => 'key',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Activity Logs',
                'route' => 'admin.activity_logs.index',
                'slug' => 'activity-logs',
                'icon' => 'file-text',
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'Menus',
                'route' => 'admin.menus.index',
                'slug' => 'menus',
                'icon' => 'settings',
                'sort_order' => 5,
                'is_active' => true,
            ],
        ];

        // Create Basic Permissions first
        $now = now();
        $permissions = [
            [
                'name' => 'View Users',
                'slug' => 'view-users',
                'description' => null,
                'module' => 'users',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Create User',
                'slug' => 'create-user',
                'description' => null,
                'module' => 'users',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Edit User',
                'slug' => 'edit-user',
                'description' => null,
                'module' => 'users',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Delete User',
                'slug' => 'delete-user',
                'description' => null,
                'module' => 'users',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'View Roles',
                'slug' => 'view-roles',
                'description' => null,
                'module' => 'roles',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Create Role',
                'slug' => 'create-role',
                'description' => null,
                'module' => 'roles',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Edit Role',
                'slug' => 'edit-role',
                'description' => null,
                'module' => 'roles',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Delete Role',
                'slug' => 'delete-role',
                'description' => null,
                'module' => 'roles',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'View Permissions',
                'slug' => 'view-permissions',
                'description' => null,
                'module' => 'permissions',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Create Permission',
                'slug' => 'create-permission',
                'description' => null,
                'module' => 'permissions',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Edit Permission',
                'slug' => 'edit-permission',
                'description' => null,
                'module' => 'permissions',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Delete Permission',
                'slug' => 'delete-permission',
                'description' => null,
                'module' => 'permissions',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'View Activity Logs',
                'slug' => 'view-activity-logs',
                'description' => null,
                'module' => 'activity_logs',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'View Menus',
                'slug' => 'view-menus',
                'description' => null,
                'module' => 'menus',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Create Menu',
                'slug' => 'create-menu',
                'description' => null,
                'module' => 'menus',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Edit Menu',
                'slug' => 'edit-menu',
                'description' => null,
                'module' => 'menus',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Delete Menu',
                'slug' => 'delete-menu',
                'description' => null,
                'module' => 'menus',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['slug' => $permission['slug']],
                $permission
            );
        }

        $this->command->info('System permissions created');

        // Get permissions for menu linking
        $viewUsersPermission = Permission::where('slug', 'view-users')->first();
        $viewRolesPermission = Permission::where('slug', 'view-roles')->first();
        $viewPermissionsPermission = Permission::where('slug', 'view-permissions')->first();
        $viewActivityLogsPermission = Permission::where('slug', 'view-activity-logs')->first();
        $viewMenusPermission = Permission::where('slug', 'view-menus')->first();

        // Create menus with permission_id
        $menuPermissionMap = [
            'users' => $viewUsersPermission ? $viewUsersPermission->id : null,
            'roles' => $viewRolesPermission ? $viewRolesPermission->id : null,
            'permissions' => $viewPermissionsPermission ? $viewPermissionsPermission->id : null,
            'activity-logs' => $viewActivityLogsPermission ? $viewActivityLogsPermission->id : null,
            'menus' => $viewMenusPermission ? $viewMenusPermission->id : null,
        ];

        foreach ($menus as $menu) {
            $permissionId = $menuPermissionMap[$menu['slug']] ?? null;
            Menu::updateOrCreate(
                ['slug' => $menu['slug']],
                array_merge($menu, ['permission_id' => $permissionId])
            );
        }

        $this->command->info('System menus created');

        // Create Administrator Role
        $administratorRole = Role::updateOrCreate(
            ['slug' => 'admin'],
            [
                'name' => 'Administrator',
                'slug' => 'admin',
                'description' => 'Full system access',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        $this->command->info('Administrator role created');

        // Assign all permissions to Administrator role
        $allPermissions = Permission::all();
        if ($allPermissions->count() > 0) {
            $administratorRole->permissions()->sync($allPermissions->pluck('id')->toArray());
            $this->command->info('All permissions assigned to Administrator role');
        }

        // Assign Administrator role to admin user
        if ($administratorRole) {
            $admin->roles()->syncWithoutDetaching([$administratorRole->id]);
            $this->command->info('Admin user assigned to Administrator role');
        }
    }
}
