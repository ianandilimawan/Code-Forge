<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Permission;

class PermissionController extends Controller
{
    public function index()
    {
        $permissions = Permission::all();
        return view('admin.pages.permissions.index', compact('permissions'));
    }

    public function create()
    {
        return view('admin.pages.permissions.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:permissions',
            'description' => 'nullable|string',
            'module' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        Permission::create($validated);

        return redirect()->route('admin.permissions.index')->with('success', 'Permission created successfully!');
    }

    public function edit(Permission $permission)
    {
        return view('admin.pages.permissions.edit', compact('permission'));
    }

    public function update(Request $request, Permission $permission)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:permissions,slug,' . $permission->id,
            'description' => 'nullable|string',
            'module' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $permission->update($validated);

        return redirect()->route('admin.permissions.index')->with('success', 'Permission updated successfully!');
    }

    public function destroy(Permission $permission)
    {
        $permission->delete();
        return redirect()->route('admin.permissions.index')->with('success', 'Permission deleted successfully!');
    }
}
