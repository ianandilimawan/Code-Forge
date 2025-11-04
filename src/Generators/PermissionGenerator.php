<?php

namespace IanAndilimawan\LaravelGenerator\Generators;

use IanAndilimawan\LaravelGenerator\Utils\FileUtil;
use Illuminate\Support\Facades\Schema;

class PermissionGenerator extends BaseGenerator
{
    public function generate(): bool
    {
        try {
            if (!Schema::hasTable('permissions')) {
                return false;
            }

            $permissions = $this->getPermissionsData();
            $createdPermissionIds = [];

            foreach ($permissions as $permission) {
                $createdPermission = $this->insertPermission($permission);
                if ($createdPermission) {
                    $createdPermissionIds[] = $createdPermission->id;
                }
            }

            // Assign permissions to administrator role
            if (!empty($createdPermissionIds)) {
                $this->assignToAdministrator($createdPermissionIds);
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function rollback(): bool
    {
        $module = $this->commandData->modelNameSnake . 's';

        // Delete permissions from database
        \App\Models\Permission::where('module', $module)->delete();

        return true;
    }

    private function getPermissionsData(): array
    {
        $module = $this->commandData->modelNameSnake . 's';
        $modelName = $this->commandData->modelName;

        return [
            [
                'name' => "View {$this->commandData->modelNamePlural}",
                'slug' => "view-{$this->commandData->modelNameSnake}s",
                'description' => "Can view {$this->commandData->modelNameLower} list",
                'module' => $module,
                'is_active' => true
            ],
            [
                'name' => "Create {$this->commandData->modelName}",
                'slug' => "create-{$this->commandData->modelNameSnake}",
                'description' => "Can create new {$this->commandData->modelNameLower}",
                'module' => $module,
                'is_active' => true
            ],
            [
                'name' => "Edit {$this->commandData->modelName}",
                'slug' => "edit-{$this->commandData->modelNameSnake}",
                'description' => "Can edit {$this->commandData->modelNameLower}",
                'module' => $module,
                'is_active' => true
            ],
            [
                'name' => "Delete {$this->commandData->modelName}",
                'slug' => "delete-{$this->commandData->modelNameSnake}",
                'description' => "Can delete {$this->commandData->modelNameLower}",
                'module' => $module,
                'is_active' => true
            ]
        ];
    }

    private function insertPermission(array $permissionData): ?\App\Models\Permission
    {
        // Use updateOrCreate to avoid duplicates
        return \App\Models\Permission::updateOrCreate(
            ['slug' => $permissionData['slug']],
            $permissionData
        );
    }

    private function assignToAdministrator(array $permissionIds): void
    {
        if (!Schema::hasTable('roles') || !Schema::hasTable('role_permission')) {
            return;
        }

        // Find administrator role by slug (try both 'administrator' and 'admin')
        $administratorRole = \App\Models\Role::whereIn('slug', ['administrator', 'admin'])->first();

        if ($administratorRole) {
            // Sync permissions without detaching existing ones
            $existingPermissionIds = $administratorRole->permissions()->pluck('permissions.id')->toArray();
            $allPermissionIds = array_unique(array_merge($existingPermissionIds, $permissionIds));
            $administratorRole->permissions()->sync($allPermissionIds);
        }
    }
}
