<?php

namespace App\Generators\Generators;

use App\Generators\Utils\FileUtil;
use Illuminate\Support\Facades\Schema;

class MenuGenerator extends BaseGenerator
{
    public function generate(): bool
    {
        try {
            if (!Schema::hasTable('menus')) {
                return false;
            }

            $menuData = $this->getMenuData();

            // Insert menu into database
            $this->insertMenu($menuData);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function rollback(): bool
    {
        $menuSlug = $this->commandData->modelNameSnake . 's';

        // Delete menu from database
        \App\Models\Menu::where('slug', $menuSlug)->delete();

        return true;
    }

    private function getMenuData(): array
    {
        // Route name dengan prefix admin. karena route dibuat di dalam Route::prefix('admin')->name('admin.')->group()
        $routeName = 'admin.' . $this->commandData->modelNameSnake . 's.index';

        // Convert model name to readable format with spaces (e.g., ProductImage -> Product Image)
        $menuName = $this->getReadableMenuName($this->commandData->modelNamePlural);

        // Find permission for this menu (view permission)
        $permissionSlug = 'view-' . $this->commandData->modelNameSnake . 's';
        $permission = \App\Models\Permission::where('slug', $permissionSlug)->first();

        // Get section_title from CommandData or auto-detect
        $sectionTitle = $this->commandData->sectionTitle ?? $this->autoDetectSectionTitle();

        return [
            'name' => $menuName,
            'slug' => $this->commandData->modelNameSnake . 's',
            'section_title' => $sectionTitle,
            'icon' => $this->getMenuIcon(),
            'route' => $routeName,
            'url' => null,
            'permission_id' => $permission ? $permission->id : null,
            'parent_id' => null,
            'sort_order' => $this->getNextSortOrder(),
            'is_active' => true
        ];
    }

    private function autoDetectSectionTitle(): ?string
    {
        $modelName = strtolower($this->commandData->modelName);

        // Auto-detect section title based on common patterns
        $sectionMap = [
            'user' => 'USER MANAGEMENT',
            'role' => 'USER MANAGEMENT',
            'permission' => 'USER MANAGEMENT',
            'product' => 'CONTENT MANAGEMENT',
            'category' => 'CONTENT MANAGEMENT',
            'post' => 'MEDIA & BLOG',
            'blog' => 'MEDIA & BLOG',
            'album' => 'MEDIA & BLOG',
            'tag' => 'MEDIA & BLOG',
            'page' => 'CONTENT MANAGEMENT',
            'banner' => 'CONTENT MANAGEMENT',
            'setting' => 'SETTINGS',
            'order' => 'E-COMMERCE',
            'transaction' => 'E-COMMERCE',
            'payment' => 'E-COMMERCE',
        ];

        // Check if model name matches any pattern
        foreach ($sectionMap as $pattern => $title) {
            if (str_contains($modelName, $pattern)) {
                return $title;
            }
        }

        // Default: assign to OTHERS if no match found
        return 'OTHERS';
    }

    private function getReadableMenuName(string $name): string
    {
        // Add space before capital letters and preserve the original capitalization
        // Example: ProductImage -> Product Image, ProductImages -> Product Images
        return preg_replace('/(?<!^)([A-Z])/', ' $1', $name);
    }

    private function getMenuIcon(): string
    {
        // Default icons based on model name
        $iconMap = [
            'product' => 'shopping-cart',
            'category' => 'folder',
            'user' => 'users',
            'order' => 'shopping-bag',
            'post' => 'file-text',
            'page' => 'file',
            'setting' => 'settings',
            'role' => 'shield',
            'permission' => 'key'
        ];

        $modelName = strtolower($this->commandData->modelName);
        return $iconMap[$modelName] ?? 'circle';
    }

    private function getNextSortOrder(): int
    {
        try {
            if (Schema::hasTable('menus')) {
                $maxOrder = \App\Models\Menu::max('sort_order') ?? 0;
                return $maxOrder + 1;
            }
        } catch (\Exception $e) {
            // Table doesn't exist, return 0
        }
        return 0;
    }

    private function insertMenu(array $menuData): void
    {
        \App\Models\Menu::create($menuData);
    }
}
