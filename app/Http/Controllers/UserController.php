<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with(['roles', 'permissions'])->get();
        return view('admin.pages.users.index', compact('users'));
    }

    public function show(User $user)
    {
        return redirect()->route('admin.users.index');
    }

    public function create()
    {
        $roles = Role::active()->with('permissions')->orderBy('name')->get();
        $permissions = Permission::active()->orderBy('module')->orderBy('name')->get();
        return view('admin.pages.users.create', compact('roles', 'permissions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'nullable|exists:roles,id',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $role = $validated['role'] ?? null;
        $permissions = $validated['permissions'] ?? [];
        unset($validated['role'], $validated['permissions']);

        $user = User::create($validated);

        if ($role) {
            $user->roles()->sync([$role]);
        } else {
            $user->roles()->sync([]);
        }
        $user->permissions()->sync($permissions);

        return redirect()->route('admin.users.index')->with('success', 'User created successfully!');
    }

    public function edit(User $user)
    {
        $roles = Role::active()->with('permissions')->orderBy('name')->get();
        $permissions = Permission::active()->orderBy('module')->orderBy('name')->get();
        $user->load(['roles', 'permissions']);
        return view('admin.pages.users.edit', compact('user', 'roles', 'permissions'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'nullable|exists:roles,id',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $role = $validated['role'] ?? null;
        $permissions = $validated['permissions'] ?? [];
        unset($validated['role'], $validated['permissions']);

        $user->update($validated);

        if ($role) {
            $user->roles()->sync([$role]);
        } else {
            $user->roles()->sync([]);
        }
        $user->permissions()->sync($permissions);

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully!');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully!');
    }
}
