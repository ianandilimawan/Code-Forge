@extends('admin.layouts.app')

@section('content')
    <div class="space-y-6">
        <!-- Page Header -->
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Edit Menu</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Update menu information</p>
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
            <form action="{{ route('admin.menus.update', $menu) }}" method="POST" class="p-8 space-y-6">
                @csrf
                @method('PUT')

                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Name
                        <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name', $menu->name) }}" required
                        class="block w-full rounded-lg border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white shadow-sm dark:shadow-md focus:border-blue-500 focus:ring-2 focus:ring-blue-500 px-4 py-4 transition-colors">
                </div>

                <!-- Slug -->
                <div>
                    <label for="slug" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Slug
                        <span class="text-red-500">*</span></label>
                    <input type="text" name="slug" id="slug" value="{{ old('slug', $menu->slug) }}" required
                        class="block w-full rounded-lg border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white shadow-sm dark:shadow-md focus:border-blue-500 focus:ring-2 focus:ring-blue-500 px-4 py-4 transition-colors">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Lowercase with hyphens (e.g., products,
                        user-management)</p>
                </div>

                <!-- Section Title -->
                <div>
                    <label for="section_title"
                        class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Section Title</label>
                    <div class="space-y-2">
                        @if (!empty($existingSections))
                            <select name="section_title" id="section_title"
                                class="block w-full rounded-lg border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white shadow-sm dark:shadow-md focus:border-blue-500 focus:ring-2 focus:ring-blue-500 px-4 py-4 transition-colors">
                                <option value="">-- No Section Title (Auto-detect) --</option>
                                @foreach ($existingSections as $section)
                                    <option value="{{ $section }}"
                                        {{ old('section_title', $menu->section_title) == $section ? 'selected' : '' }}>
                                        {{ $section }}
                                    </option>
                                @endforeach
                                @if (!in_array($menu->section_title, $existingSections) && $menu->section_title)
                                    <option value="{{ $menu->section_title }}" selected>{{ $menu->section_title }} (Current)
                                    </option>
                                @endif
                                <option value="__new__">-- Create New Section Title --</option>
                            </select>
                        @endif
                        <input type="text" name="section_title_new" id="section_title_new"
                            value="{{ old('section_title_new', !in_array($menu->section_title, $existingSections ?? []) && $menu->section_title ? $menu->section_title : '') }}"
                            placeholder="Or enter new section title (e.g., CONTENT MANAGEMENT)"
                            class="block w-full rounded-lg border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white shadow-sm dark:shadow-md focus:border-blue-500 focus:ring-2 focus:ring-blue-500 px-4 py-4 transition-colors {{ !empty($existingSections) ? 'hidden' : '' }}">
                    </div>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Group this menu with others under the same
                        section title</p>
                </div>

                <!-- Icon -->
                <div>
                    <label for="icon"
                        class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Icon</label>
                    <input type="text" name="icon" id="icon" value="{{ old('icon', $menu->icon) }}"
                        placeholder="e.g., home, users, settings, shopping-cart"
                        class="block w-full rounded-lg border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white shadow-sm dark:shadow-md focus:border-blue-500 focus:ring-2 focus:ring-blue-500 px-4 py-4 transition-colors">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Available icons: home, users, settings, shield,
                        key, shopping-cart, folder, file-text, etc.</p>
                </div>

                <!-- Route -->
                <div>
                    <label for="route"
                        class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Route</label>
                    <input type="text" name="route" id="route" value="{{ old('route', $menu->route) }}"
                        placeholder="e.g., admin.products.index"
                        class="block w-full rounded-lg border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white shadow-sm dark:shadow-md focus:border-blue-500 focus:ring-2 focus:ring-blue-500 px-4 py-4 transition-colors">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Laravel route name (e.g., admin.products.index)
                    </p>
                </div>

                <!-- URL -->
                <div>
                    <label for="url" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">URL
                        (Alternative)</label>
                    <input type="text" name="url" id="url" value="{{ old('url', $menu->url) }}"
                        placeholder="e.g., /admin/products or https://example.com"
                        class="block w-full rounded-lg border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white shadow-sm dark:shadow-md focus:border-blue-500 focus:ring-2 focus:ring-blue-500 px-4 py-4 transition-colors">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Use if route is not available. Either route or
                        URL must be provided.</p>
                </div>

                <!-- Permission -->
                <div>
                    <label for="permission_id"
                        class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Permission</label>
                    <select name="permission_id" id="permission_id"
                        class="block w-full rounded-lg border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white shadow-sm dark:shadow-md focus:border-blue-500 focus:ring-2 focus:ring-blue-500 px-4 py-4 transition-colors">
                        <option value="">-- No Permission Required --</option>
                        @foreach ($permissions as $permission)
                            <option value="{{ $permission->id }}"
                                {{ old('permission_id', $menu->permission_id) == $permission->id ? 'selected' : '' }}>
                                {{ $permission->name }} ({{ $permission->slug }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Parent Menu -->
                <div>
                    <label for="parent_id" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Parent
                        Menu</label>
                    <select name="parent_id" id="parent_id"
                        class="block w-full rounded-lg border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white shadow-sm dark:shadow-md focus:border-blue-500 focus:ring-2 focus:ring-blue-500 px-4 py-4 transition-colors">
                        <option value="">-- No Parent (Root Menu) --</option>
                        @foreach ($parentMenus as $parentMenu)
                            <option value="{{ $parentMenu->id }}"
                                {{ old('parent_id', $menu->parent_id) == $parentMenu->id ? 'selected' : '' }}>
                                {{ $parentMenu->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Sort Order -->
                <div>
                    <label for="sort_order" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Sort
                        Order</label>
                    <input type="number" name="sort_order" id="sort_order"
                        value="{{ old('sort_order', $menu->sort_order) }}" min="0"
                        class="block w-full rounded-lg border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white shadow-sm dark:shadow-md focus:border-blue-500 focus:ring-2 focus:ring-blue-500 px-4 py-4 transition-colors">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Lower numbers appear first</p>
                </div>

                <!-- Is Active -->
                <div>
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" name="is_active" id="is_active" value="1"
                            {{ old('is_active', $menu->is_active) ? 'checked' : '' }}
                            class="w-5 h-5 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                        <span class="ml-3 text-sm font-semibold text-gray-700 dark:text-gray-300">Active</span>
                    </label>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end gap-4 pt-8 border-t-2 border-gray-100 dark:border-gray-700 mt-8">
                    <a href="{{ route('admin.menus.index') }}"
                        class="px-8 py-3 bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors font-semibold shadow-md hover:shadow-lg border-2 border-gray-200 dark:border-gray-600">
                        Cancel
                    </a>
                    <button type="submit"
                        class="px-8 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors font-semibold shadow-md hover:shadow-lg hover:scale-105 transform">
                        Update Menu
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sectionTitleSelect = document.getElementById('section_title');
            const sectionTitleNew = document.getElementById('section_title_new');

            if (sectionTitleSelect) {
                sectionTitleSelect.addEventListener('change', function() {
                    if (this.value === '__new__') {
                        sectionTitleNew.classList.remove('hidden');
                        sectionTitleNew.setAttribute('name', 'section_title');
                        sectionTitleSelect.setAttribute('name', 'section_title_old');
                    } else {
                        sectionTitleNew.classList.add('hidden');
                        sectionTitleNew.removeAttribute('name');
                        sectionTitleSelect.setAttribute('name', 'section_title');
                    }
                });
            }
        });
    </script>
@endsection
