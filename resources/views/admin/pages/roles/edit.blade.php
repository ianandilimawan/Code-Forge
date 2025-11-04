@extends('admin.layouts.app')

@section('content')
    <div class="space-y-6">
        <!-- Page Header -->
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Edit Role</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Update role information</p>
        </div>

        @if (session('success'))
            <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl p-4">
                <p class="text-sm text-green-600 dark:text-green-400">{{ session('success') }}</p>
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-4">
                <ul class="text-sm text-red-600 dark:text-red-400">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Form -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700">
            <form action="{{ route('admin.roles.update', $role) }}" method="POST" class="p-8 space-y-6">
                @csrf
                @method('PUT')

                <!-- Name -->
                <div>
                    <label for="name"
                        class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $role->name) }}" required
                        class="block w-full rounded-lg border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white shadow-sm dark:shadow-md focus:border-blue-500 focus:ring-2 focus:ring-blue-500 px-4 py-4 transition-colors">
                </div>

                <!-- Slug -->
                <div>
                    <label for="slug"
                        class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Slug</label>
                    <input type="text" name="slug" id="slug" value="{{ old('slug', $role->slug) }}" required
                        class="block w-full rounded-lg border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white shadow-sm dark:shadow-md focus:border-blue-500 focus:ring-2 focus:ring-blue-500 px-4 py-4 transition-colors">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Lowercase with hyphens (e.g., admin, editor)
                    </p>
                </div>

                <!-- Description -->
                <div>
                    <label for="description"
                        class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Description</label>
                    <textarea name="description" id="description" rows="3"
                        class="block w-full rounded-lg border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white shadow-sm dark:shadow-md focus:border-blue-500 focus:ring-2 focus:ring-blue-500 px-4 py-4 transition-colors">{{ old('description', $role->description) }}</textarea>
                </div>

                <!-- Is Active -->
                <div>
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" name="is_active" id="is_active" value="1"
                            {{ old('is_active', $role->is_active) ? 'checked' : '' }}
                            class="w-5 h-5 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                        <span class="ml-3 text-sm font-semibold text-gray-700 dark:text-gray-300">Active</span>
                    </label>
                </div>

                <!-- Permissions -->
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">Permissions</label>
                        @if (!$permissions->isEmpty())
                            <label class="flex items-center cursor-pointer">
                                <input type="checkbox" id="select-all-permissions"
                                    class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <span class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">Select All</span>
                            </label>
                        @endif
                    </div>
                    <div
                        class="space-y-4 max-h-96 overflow-y-auto border-2 border-gray-200 dark:border-gray-700 rounded-lg p-4">
                        @if ($permissions->isEmpty())
                            <p class="text-sm text-gray-500 dark:text-gray-400">No permissions available</p>
                        @else
                            @php
                                $groupedPermissions = $permissions->groupBy('module');
                                $rolePermissionIds = $role->permissions->pluck('id')->toArray();
                            @endphp
                            @foreach ($groupedPermissions as $module => $modulePermissions)
                                <div
                                    class="mb-4 pb-4 border-b-2 border-gray-100 dark:border-gray-700 last:border-b-0 last:mb-0 last:pb-0">
                                    <div class="flex items-center justify-between mb-3">
                                        @if ($module)
                                            <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200 uppercase">
                                                {{ $module }}</h3>
                                        @else
                                            <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200">Other</h3>
                                        @endif
                                        <label class="flex items-center cursor-pointer">
                                            <input type="checkbox"
                                                class="select-all-module w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                                                data-module="{{ $module ?? 'other' }}">
                                            <span class="ml-2 text-xs font-medium text-gray-600 dark:text-gray-400">Select
                                                All</span>
                                        </label>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                        @foreach ($modulePermissions as $permission)
                                            <label
                                                class="flex items-center cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 p-2 rounded-lg transition-colors permission-checkbox"
                                                data-module="{{ $module ?? 'other' }}">
                                                <input type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                                                    {{ in_array($permission->id, old('permissions', $rolePermissionIds)) ? 'checked' : '' }}
                                                    class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                                <span
                                                    class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ $permission->name }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Select which permissions this role should have
                    </p>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end gap-4 pt-8 border-t-2 border-gray-100 dark:border-gray-700 mt-8">
                    <a href="{{ route('admin.roles.index') }}"
                        class="px-8 py-3 bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors font-semibold shadow-md hover:shadow-lg border-2 border-gray-200 dark:border-gray-600">
                        Cancel
                    </a>
                    <button type="submit"
                        class="px-8 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors font-semibold shadow-md hover:shadow-lg hover:scale-105 transform">
                        Update Role
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectAllCheckbox = document.getElementById('select-all-permissions');
            const moduleCheckboxes = document.querySelectorAll('.select-all-module');
            const permissionCheckboxes = document.querySelectorAll('input[name="permissions[]"]');

            // Select All functionality
            if (selectAllCheckbox) {
                selectAllCheckbox.addEventListener('change', function() {
                    permissionCheckboxes.forEach(checkbox => {
                        checkbox.checked = this.checked;
                    });

                    // Update module checkboxes
                    moduleCheckboxes.forEach(moduleCheckbox => {
                        moduleCheckbox.checked = this.checked;
                    });
                });
            }

            // Module Select All functionality
            moduleCheckboxes.forEach(moduleCheckbox => {
                moduleCheckbox.addEventListener('change', function() {
                    const module = this.getAttribute('data-module');
                    const modulePermissionCheckboxes = document.querySelectorAll(
                        `.permission-checkbox[data-module="${module}"] input[type="checkbox"]`
                    );

                    modulePermissionCheckboxes.forEach(checkbox => {
                        checkbox.checked = this.checked;
                    });

                    // Update main select all checkbox
                    updateSelectAllCheckbox();
                });
            });

            // Update main select all checkbox
            function updateSelectAllCheckbox() {
                if (selectAllCheckbox) {
                    const allChecked = Array.from(permissionCheckboxes).every(cb => cb.checked);
                    const someChecked = Array.from(permissionCheckboxes).some(cb => cb.checked);
                    selectAllCheckbox.checked = allChecked;
                    selectAllCheckbox.indeterminate = someChecked && !allChecked;
                }
            }

            // Update module checkboxes
            function updateModuleCheckboxes() {
                moduleCheckboxes.forEach(moduleCheckbox => {
                    const module = moduleCheckbox.getAttribute('data-module');
                    const modulePermissionCheckboxes = document.querySelectorAll(
                        `.permission-checkbox[data-module="${module}"] input[type="checkbox"]`
                    );
                    const allChecked = Array.from(modulePermissionCheckboxes).every(cb => cb.checked);
                    const someChecked = Array.from(modulePermissionCheckboxes).some(cb => cb.checked);
                    moduleCheckbox.checked = allChecked;
                    moduleCheckbox.indeterminate = someChecked && !allChecked;
                });
            }

            // Individual checkbox change
            permissionCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    updateSelectAllCheckbox();
                    updateModuleCheckboxes();
                });
            });

            // Initialize checkboxes state
            updateSelectAllCheckbox();
            updateModuleCheckboxes();
        });
    </script>
@endsection
