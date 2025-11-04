@extends('admin.layouts.app')

@section('content')
    <div class="space-y-6">
        @if (session('success'))
            <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl p-4">
                <p class="text-sm text-green-600 dark:text-green-400">{{ session('success') }}</p>
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-4">
                <p class="text-sm text-red-600 dark:text-red-400">{{ session('error') }}</p>
            </div>
        @endif

        <!-- Page Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Users</h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Manage your users here</p>
            </div>
            @if (auth()->user() && auth()->user()->hasPermission('create-user'))
                <a href="{{ route('admin.users.create') }}"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Add User
                </a>
            @endif
        </div>

        <!-- Users Table -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table id="usersTable" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 display"
                    style="width:100%">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                User
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Email
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Role
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
                        @forelse($users as $user)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex items-center">
                                            <img class="h-10 w-10 rounded-full hidden lg:block"
                                                src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=3b82f6&color=fff"
                                                alt="">
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                    {{ $user->name }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 dark:text-white">{{ $user->email }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-wrap gap-1">
                                        @forelse($user->roles as $role)
                                            <span
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                                                {{ $role->name }}
                                            </span>
                                        @empty
                                            <span
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300">
                                                No Role
                                            </span>
                                        @endforelse
                                    </div>
                                    @if ($user->permissions->isNotEmpty())
                                        <div class="mt-1">
                                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                                + {{ $user->permissions->count() }} direct permission(s)
                                            </span>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                                        Active
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    @if (auth()->user() && auth()->user()->hasPermission('edit-user'))
                                        <a href="{{ route('admin.users.edit', $user) }}"
                                            class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 mr-3">Edit</a>
                                    @endif
                                    @if (auth()->user() && auth()->user()->hasPermission('delete-user'))
                                        <button onclick="confirmDelete({{ $user->id }})"
                                            class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">Delete</button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                    No users found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <x-admin.confirm-delete-modal id="deleteModal" title="Delete User"
        message="Are you sure you want to delete this user? This action cannot be undone." />

    @push('scripts')
        <script>
            let userIdToDelete = null;

            function confirmDelete(userId) {
                userIdToDelete = userId;
                document.getElementById('deleteModal').classList.remove('hidden');
            }

            function closeModal(modalId) {
                document.getElementById(modalId).classList.add('hidden');
                userIdToDelete = null;
            }

            function processDelete() {
                if (userIdToDelete) {
                    // Create form to submit delete request
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '/admin/users/' + userIdToDelete;

                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_token';
                    csrfInput.value = '{{ csrf_token() }}';

                    const methodInput = document.createElement('input');
                    methodInput.type = 'hidden';
                    methodInput.name = '_method';
                    methodInput.value = 'DELETE';

                    form.appendChild(csrfInput);
                    form.appendChild(methodInput);
                    document.body.appendChild(form);
                    form.submit();
                }
            }
        </script>

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

            #usersTable {
                padding: 0 1.5rem 1.5rem 1.5rem;
            }

            #usersTable td {
                padding: 1rem 1.5rem;
            }

            #usersTable th {
                padding: 1rem 1.5rem;
            }

            .dataTables_wrapper {
                font-size: 0.875rem;
            }
        </style>
        <script>
            $(document).ready(function() {
                $('#usersTable').DataTable({
                    pageLength: 10,
                    lengthMenu: [
                        [10, 20, 50, 100, -1],
                        [10, 20, 50, 100, "All"]
                    ],
                    language: {
                        search: "_INPUT_",
                        searchPlaceholder: "Search users..."
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
