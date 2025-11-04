@extends('admin.layouts.app')

@section('content')
    <div class="space-y-6">
        <!-- Page Header -->
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Edit Permission</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Update permission information</p>
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
            <form action="{{ route('admin.permissions.update', $permission) }}" method="POST" class="p-8 space-y-6">
                @csrf
                @method('PUT')

                <!-- Name -->
                <div>
                    <label for="name"
                        class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $permission->name) }}"
                        required
                        class="block w-full rounded-lg border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white shadow-sm dark:shadow-md focus:border-blue-500 focus:ring-2 focus:ring-blue-500 px-4 py-4 transition-colors">
                </div>

                <!-- Slug -->
                <div>
                    <label for="slug"
                        class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Slug</label>
                    <input type="text" name="slug" id="slug" value="{{ old('slug', $permission->slug) }}"
                        required
                        class="block w-full rounded-lg border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white shadow-sm dark:shadow-md focus:border-blue-500 focus:ring-2 focus:ring-blue-500 px-4 py-4 transition-colors">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Lowercase with hyphens (e.g., view-users)</p>
                </div>

                <!-- Module -->
                <div>
                    <label for="module"
                        class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Module</label>
                    <input type="text" name="module" id="module" value="{{ old('module', $permission->module) }}"
                        class="block w-full rounded-lg border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white shadow-sm dark:shadow-md focus:border-blue-500 focus:ring-2 focus:ring-blue-500 px-4 py-4 transition-colors">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Optional module/feature name (e.g., users)</p>
                </div>

                <!-- Description -->
                <div>
                    <label for="description"
                        class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Description</label>
                    <textarea name="description" id="description" rows="3"
                        class="block w-full rounded-lg border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white shadow-sm dark:shadow-md focus:border-blue-500 focus:ring-2 focus:ring-blue-500 px-4 py-4 transition-colors">{{ old('description', $permission->description) }}</textarea>
                </div>

                <!-- Is Active -->
                <div>
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" name="is_active" id="is_active" value="1"
                            {{ old('is_active', $permission->is_active) ? 'checked' : '' }}
                            class="w-5 h-5 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                        <span class="ml-3 text-sm font-semibold text-gray-700 dark:text-gray-300">Active</span>
                    </label>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end gap-4 pt-8 border-t-2 border-gray-100 dark:border-gray-700 mt-8">
                    <a href="{{ route('admin.permissions.index') }}"
                        class="px-8 py-3 bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors font-semibold shadow-md hover:shadow-lg border-2 border-gray-200 dark:border-gray-600">
                        Cancel
                    </a>
                    <button type="submit"
                        class="px-8 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors font-semibold shadow-md hover:shadow-lg hover:scale-105 transform">
                        Update Permission
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
