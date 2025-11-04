<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\Permission;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::all();
        return view('admin.pages.roles.index', compact('roles'));
    }

    public function create()
    {
        $permissions = Permission::active()->orderBy('module')->orderBy('name')->get();
        return view('admin.pages.roles.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:roles',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $permissions = $validated['permissions'] ?? [];
        unset($validated['permissions']);

        $role = Role::create($validated);

        $role->permissions()->sync($permissions);

        return redirect()->route('admin.roles.index')->with('success', 'Role created successfully!');
    }

    public function edit(Role $role)
    {
        $permissions = Permission::active()->orderBy('module')->orderBy('name')->get();
        $role->load('permissions');
        return view('admin.pages.roles.edit', compact('role', 'permissions'));
    }

    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:roles,slug,' . $role->id,
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $permissions = $validated['permissions'] ?? [];
        unset($validated['permissions']);

        $role->update($validated);

        $role->permissions()->sync($permissions);

        return redirect()->route('admin.roles.index')->with('success', 'Role updated successfully!');
    }

    public function destroy(Role $role)
    {
        $role->delete();
        return redirect()->route('admin.roles.index')->with('success', 'Role deleted successfully!');
    }
}
