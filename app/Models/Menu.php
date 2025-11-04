<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Menu extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'section_title',
        'icon',
        'route',
        'url',
        'permission_id',
        'parent_id',
        'sort_order',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'parent_id' => 'integer'
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Menu::class, 'parent_id')->orderBy('sort_order');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    public function permission()
    {
        return $this->belongsTo(Permission::class);
    }

    public function scopeAccessibleBy($query, $user)
    {
        // If user is null or not authenticated, return empty
        if (!$user) {
            return $query->whereRaw('1 = 0'); // Return empty result
        }

        // Administrator role has access to all menus
        if ($user->hasRole('administrator') || $user->hasRole('admin')) {
            return $query; // Return all menus without filtering
        }

        // Get permission IDs that user has
        // Priority: If user has direct permissions, use only those
        // Otherwise, use permissions from role
        $userPermissionIds = $user->permissions()->pluck('permissions.id')->toArray();

        // Only use role permissions if user has no direct permissions
        if (empty($userPermissionIds)) {
            $rolePermissionIds = $user->roles()->with('permissions')->get()
                ->flatMap->permissions->pluck('id')->unique()->toArray();
            $allPermissionIds = array_unique($rolePermissionIds);
        } else {
            // User has direct permissions - use only those (override role permissions)
            $allPermissionIds = $userPermissionIds;
        }

        // Menu without permission_id is accessible only if:
        // - It's Dashboard route (general access)
        // For other menus, permission_id is required and must match user's permissions
        return $query->where(function ($q) use ($allPermissionIds) {
            $q->where(function ($subQ) {
                // Dashboard is accessible by all authenticated users
                $subQ->whereNull('permission_id')
                    ->where(function ($dashboardQ) {
                        $dashboardQ->where('route', 'admin.dashboard')
                            ->orWhere('route', 'admin.index')
                            ->orWhereNull('route');
                    });
            });

            // If user has any permissions, also show menus with those permissions
            if (!empty($allPermissionIds)) {
                $q->orWhereIn('permission_id', $allPermissionIds);
            }
        });
    }
}
