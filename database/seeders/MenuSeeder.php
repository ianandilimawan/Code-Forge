<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Menu;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        $menus = [
            [
                'name' => 'Dashboard',
                'slug' => 'dashboard',
                'section_title' => null,
                'icon' => 'home',
                'route' => 'admin.dashboard',
                'url' => null,
                'permission_id' => null,
                'parent_id' => null,
                'sort_order' => 0,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Users',
                'slug' => 'users',
                'section_title' => 'USER MANAGEMENT',
                'icon' => 'users',
                'route' => 'admin.users.index',
                'url' => null,
                'permission_id' => 1,
                'parent_id' => null,
                'sort_order' => 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Roles',
                'slug' => 'roles',
                'section_title' => 'USER MANAGEMENT',
                'icon' => 'shield',
                'route' => 'admin.roles.index',
                'url' => null,
                'permission_id' => 5,
                'parent_id' => null,
                'sort_order' => 2,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Permissions',
                'slug' => 'permissions',
                'section_title' => 'USER MANAGEMENT',
                'icon' => 'key',
                'route' => 'admin.permissions.index',
                'url' => null,
                'permission_id' => 9,
                'parent_id' => null,
                'sort_order' => 3,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        foreach ($menus as $menu) {
            Menu::updateOrCreate(
                ['slug' => $menu['slug']],
                $menu
            );
        }
    }
}
