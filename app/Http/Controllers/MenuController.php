<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Menu;
use App\Models\Permission;

class MenuController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = \Illuminate\Support\Facades\Auth::user();

            if (!$user) {
                abort(403);
            }

            // Administrator role has access to all actions
            if ($user->hasRole('administrator') || $user->hasRole('admin')) {
                return $next($request);
            }

            $routeName = $request->route()?->getName();
            $modelNameSnake = 'menu';

            // Skip permission check for custom routes
            $customRoutes = ['icon-preview', 'update-sort-order', 'quick-update'];
            if ($routeName && in_array(str_replace("admin.menus.", "", $routeName), $customRoutes)) {
                return $next($request);
            }

            if ($routeName) {
                if (str_contains($routeName, '.index') || str_contains($routeName, '.show')) {
                    abort_unless($user->hasPermission("view-{$modelNameSnake}s"), 403);
                } elseif (str_contains($routeName, '.create') || str_contains($routeName, '.store')) {
                    abort_unless($user->hasPermission("create-{$modelNameSnake}"), 403);
                } elseif (str_contains($routeName, '.edit') || str_contains($routeName, '.update')) {
                    abort_unless($user->hasPermission("edit-{$modelNameSnake}"), 403);
                } elseif (str_contains($routeName, '.destroy')) {
                    abort_unless($user->hasPermission("delete-{$modelNameSnake}"), 403);
                }
            }

            return $next($request);
        });
    }
    public function index()
    {
        $menus = Menu::with('permission')
            ->orderBy('section_title')
            ->orderBy('sort_order')
            ->get();

        // Group menus by section_title for display
        $groupedMenus = $menus->groupBy(function ($menu) {
            return $menu->section_title ?? 'Main Menu';
        });

        // Get existing section titles for dropdown
        $existingSections = Menu::whereNotNull('section_title')
            ->distinct()
            ->pluck('section_title')
            ->sort()
            ->toArray();

        return view('admin.pages.menus.index', compact('menus', 'groupedMenus', 'existingSections'));
    }

    public function updateSortOrder(Request $request)
    {
        $validated = $request->validate([
            'menu_ids' => 'required|array',
            'menu_ids.*' => 'exists:menus,id',
        ]);

        // Update sort order based on the new order
        // This will maintain section grouping but allow reordering within and across sections
        foreach ($validated['menu_ids'] as $index => $menuId) {
            Menu::where('id', $menuId)->update(['sort_order' => $index]);
        }

        return response()->json(['success' => true, 'message' => 'Menu order updated successfully']);
    }

    public function quickUpdate(Request $request, Menu $menu)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'section_title' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:255',
            'route' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        // Handle section_title_new (when creating new section)
        if (!empty($request->section_title_new)) {
            $validated['section_title'] = $request->section_title_new;
        }

        $menu->update($validated);

        return response()->json(['success' => true, 'message' => 'Menu updated successfully', 'menu' => $menu->fresh()]);
    }

    public function iconPreview(Request $request)
    {
        $iconCode = $request->get('icon', '');
        return response()->make(\App\Helpers\MenuHelper::renderIcon($iconCode));
    }

    public function create()
    {
        $permissions = Permission::active()->orderBy('module')->orderBy('name')->get();
        $parentMenus = Menu::root()->orderBy('name')->get();

        // Get existing section titles for dropdown
        $existingSections = Menu::whereNotNull('section_title')
            ->distinct()
            ->pluck('section_title')
            ->sort()
            ->toArray();

        return view('admin.pages.menus.create', compact('permissions', 'parentMenus', 'existingSections'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:menus',
            'section_title' => 'nullable|string|max:255',
            'section_title_new' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:255',
            'route' => 'nullable|string|max:255',
            'url' => 'nullable|string|max:255',
            'permission_id' => 'nullable|exists:permissions,id',
            'parent_id' => 'nullable|exists:menus,id',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        // Handle section_title_new (when creating new section)
        if (!empty($validated['section_title_new'])) {
            $validated['section_title'] = $validated['section_title_new'];
        }
        unset($validated['section_title_new']);

        // Ensure either route or url is provided
        if (empty($validated['route']) && empty($validated['url'])) {
            $validated['url'] = '#';
        }

        Menu::create($validated);

        return redirect()->route('admin.menus.index')->with('success', 'Menu created successfully!');
    }

    public function edit(Menu $menu)
    {
        $permissions = Permission::active()->orderBy('module')->orderBy('name')->get();
        $parentMenus = Menu::where('id', '!=', $menu->id)
            ->root()
            ->orderBy('name')
            ->get();

        // Get existing section titles for dropdown
        $existingSections = Menu::whereNotNull('section_title')
            ->where('id', '!=', $menu->id)
            ->distinct()
            ->pluck('section_title')
            ->sort()
            ->toArray();

        return view('admin.pages.menus.edit', compact('menu', 'permissions', 'parentMenus', 'existingSections'));
    }

    public function update(Request $request, Menu $menu)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:menus,slug,' . $menu->id,
            'section_title' => 'nullable|string|max:255',
            'section_title_new' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:255',
            'route' => 'nullable|string|max:255',
            'url' => 'nullable|string|max:255',
            'permission_id' => 'nullable|exists:permissions,id',
            'parent_id' => 'nullable|exists:menus,id|different:' . $menu->id,
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        // Handle section_title_new (when creating new section)
        if (!empty($validated['section_title_new'])) {
            $validated['section_title'] = $validated['section_title_new'];
        }
        unset($validated['section_title_new']);

        // Ensure either route or url is provided
        if (empty($validated['route']) && empty($validated['url'])) {
            $validated['url'] = '#';
        }

        $menu->update($validated);

        return redirect()->route('admin.menus.index')->with('success', 'Menu updated successfully!');
    }

    public function destroy(Menu $menu)
    {
        // Prevent deleting menu with children
        if ($menu->children()->exists()) {
            return redirect()->route('admin.menus.index')
                ->with('error', 'Cannot delete menu with child menus. Please delete child menus first.');
        }

        $menu->delete();

        return redirect()->route('admin.menus.index')->with('success', 'Menu deleted successfully!');
    }
}
