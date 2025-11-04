<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Menu;
use App\Models\Permission;

class UpdateMenusPermission extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'menus:update-permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update existing menus with permission_id based on their routes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Updating menus with permissions...');

        $menus = Menu::whereNull('permission_id')->get();
        $updated = 0;

        foreach ($menus as $menu) {
            if (!$menu->route) {
                continue;
            }

            // Extract model name from route (e.g., admin.products.index -> products)
            // Route format: admin.{model}s.index
            if (preg_match('/admin\.(.+?)s\.index/', $menu->route, $matches)) {
                $modelName = $matches[1];

                // Handle special cases like product_images -> product_images
                // For product_images, the route is admin.product_images.index
                if (str_contains($modelName, '_')) {
                    // Already in snake_case format
                } else {
                    // Convert to snake_case if needed
                    $modelName = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $modelName));
                }

                // Find permission with slug: view-{model}s
                $permissionSlug = 'view-' . $modelName . 's';
                $permission = Permission::where('slug', $permissionSlug)->first();

                if ($permission) {
                    $menu->permission_id = $permission->id;
                    $menu->save();
                    $updated++;
                    $this->info("  ✓ Updated menu '{$menu->name}' with permission '{$permission->name}'");
                } else {
                    $this->warn("  ⚠ Menu '{$menu->name}' - Permission '{$permissionSlug}' not found");
                }
            } else {
                // Special handling for Dashboard and other non-model routes
                // Dashboard and system menus can remain without permission_id (accessible by all)
                if (in_array($menu->route, ['admin.dashboard', 'admin.index'])) {
                    $this->info("  ℹ Menu '{$menu->name}' - Dashboard menu, keeping without permission");
                } else {
                    $this->warn("  ⚠ Menu '{$menu->name}' - Route '{$menu->route}' doesn't match expected format");
                }
            }
        }

        $this->info("\n✓ Updated {$updated} menu(s)");
        return Command::SUCCESS;
    }
}
