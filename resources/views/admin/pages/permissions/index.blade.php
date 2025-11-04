@extends('admin.layouts.app')

@section('content')
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Permissions</h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Manage system permissions</p>
            </div>
            @if (auth()->user() && auth()->user()->hasPermission('create-permission'))
                <a href="{{ route('admin.permissions.create') }}"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors inline-flex items-center">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Add Permission
                </a>
            @endif
        </div>

        @if (session('success'))
            <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl p-4">
                <p class="text-sm text-green-600 dark:text-green-400">{{ session('success') }}</p>
            </div>
        @endif

        <!-- Permissions Table -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table id="permissionsTable" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 display"
                    style="width:100%">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Permission
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Slug
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Module
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Status
                            </th>
                            <th
                                class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($permissions as $permission)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $permission->name }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ $permission->slug }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($permission->module)
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300 capitalize">
                                            {{ $permission->module }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $permission->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300' }}">
                                        {{ $permission->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    @if (auth()->user() && auth()->user()->hasPermission('edit-permission'))
                                        <a href="{{ route('admin.permissions.edit', $permission) }}"
                                            class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 mr-3">Edit</a>
                                    @endif
                                    @if (auth()->user() && auth()->user()->hasPermission('delete-permission'))
                                        <form action="{{ route('admin.permissions.destroy', $permission) }}" method="POST"
                                            class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                onclick="return confirm('Are you sure you want to delete this permission?')"
                                                class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">Delete</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                    No permissions found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
        <style>
            /* DataTables Custom Styling */
            .dataTables_wrapper .dataTables_length select {
                padding: 0.5rem 2rem 0.5rem 0.75rem;
                border-radius: 0.375rem;
                border: 1px solid #d1d5db;
                background-color: white;
            }

            .dark .dataTables_wrapper .dataTables_length select {
                border-color: #4b5563;
                background-color: #1f2937;
                color: #f9fafb;
            }

            .dataTables_wrapper .dataTables_filter input {
                padding: 0.5rem 0.75rem;
                border-radius: 0.375rem;
                border: 1px solid #d1d5db;
                margin-left: 0.5rem;
            }

            .dark .dataTables_wrapper .dataTables_filter input {
                border-color: #4b5563;
                background-color: #1f2937;
                color: #f9fafb;
            }

            .dataTables_wrapper .dataTables_length,
            .dataTables_wrapper .dataTables_filter {
                padding: 1.5rem 1.5rem 1rem 1.5rem;
            }

            .dataTables_wrapper .dataTables_paginate {
                padding: 1rem 1.5rem;
            }

            .dataTables_wrapper .dataTables_info {
                padding: 1rem 1.5rem;
            }

            #permissionsTable td {
                padding: 1rem 1.5rem;
            }

            #permissionsTable th {
                padding: 1rem 1.5rem;
            }

            .dataTables_wrapper {
                font-size: 0.875rem;
            }
        </style>
        <script>
            $(document).ready(function() {
                $('#permissionsTable').DataTable({
                    pageLength: 10,
                    lengthMenu: [
                        [10, 20, 50, 100, -1],
                        [10, 20, 50, 100, "All"]
                    ],
                    language: {
                        search: "_INPUT_",
                        searchPlaceholder: "Search permissions..."
                    },
                    order: [
                        [0, 'asc']
                    ],
                    columnDefs: [{
                        orderable: false,
                        targets: 4
                    }],
                    dom: '<"flex justify-between items-center mb-4"lf>rt<"flex justify-between items-center mt-4"ip>'
                });
            });
        </script>
    @endpush
@endsection
