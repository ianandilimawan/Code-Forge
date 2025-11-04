@extends('admin.layouts.app')

@section('content')
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Roles</h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Manage user roles and permissions</p>
            </div>
            @if (auth()->user() && auth()->user()->hasPermission('create-role'))
                <a href="{{ route('admin.roles.create') }}"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors inline-flex items-center">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Add Role
                </a>
            @endif
        </div>

        @if (session('success'))
            <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl p-4">
                <p class="text-sm text-green-600 dark:text-green-400">{{ session('success') }}</p>
            </div>
        @endif

        <!-- Roles Table -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table id="rolesTable" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 display"
                    style="width:100%">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Role
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Slug
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Description
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
                        @forelse($roles as $role)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0">
                                            <div
                                                class="w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                                                <svg class="w-5 h-5 text-blue-600 dark:text-blue-300" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z">
                                                    </path>
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ $role->name }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ $role->slug }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900 dark:text-white">{{ $role->description ?? '-' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $role->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300' }}">
                                        {{ $role->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    @if (auth()->user() && auth()->user()->hasPermission('edit-role'))
                                        <a href="{{ route('admin.roles.edit', $role) }}"
                                            class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 mr-3">Edit</a>
                                    @endif
                                    @if (auth()->user() && auth()->user()->hasPermission('delete-role'))
                                        <form action="{{ route('admin.roles.destroy', $role) }}" method="POST"
                                            class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                onclick="return confirm('Are you sure you want to delete this role?')"
                                                class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">Delete</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                    No roles found. Create your first role!
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

            #rolesTable td {
                padding: 1rem 1.5rem;
            }

            #rolesTable th {
                padding: 1rem 1.5rem;
            }

            .dataTables_wrapper {
                font-size: 0.875rem;
            }
        </style>
        <script>
            $(document).ready(function() {
                $('#rolesTable').DataTable({
                    pageLength: 10,
                    lengthMenu: [
                        [10, 20, 50, 100, -1],
                        [10, 20, 50, 100, "All"]
                    ],
                    language: {
                        search: "_INPUT_",
                        searchPlaceholder: "Search roles..."
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
