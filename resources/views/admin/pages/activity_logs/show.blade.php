@extends('admin.layouts.app')

@section('content')
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Activity Log Details</h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">View detailed information about this activity</p>
            </div>
            <a href="{{ route('admin.activity_logs.index') }}"
                class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors inline-flex items-center">
                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18">
                    </path>
                </svg>
                Back to Logs
            </a>
        </div>

        <!-- Activity Log Details -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Activity Information</h2>
            </div>
            <div class="px-6 py-4 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Action</label>
                        <span
                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                            {{ $activityLog->action == 'create' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' : '' }}
                            {{ $activityLog->action == 'update' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300' : '' }}
                            {{ $activityLog->action == 'delete' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300' : '' }}">
                            {{ ucfirst($activityLog->action) }}
                        </span>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Model Type</label>
                        <p class="text-sm text-gray-900 dark:text-white">{{ $activityLog->model_type }}</p>
                        @if ($activityLog->model_id)
                            <p class="text-xs text-gray-500 dark:text-gray-400">ID: {{ $activityLog->model_id }}</p>
                        @endif
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">User</label>
                        @if ($activityLog->user)
                            <p class="text-sm text-gray-900 dark:text-white">{{ $activityLog->user->name }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $activityLog->user->email }}</p>
                        @else
                            <p class="text-sm text-gray-500 dark:text-gray-400">System</p>
                        @endif
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date</label>
                        <p class="text-sm text-gray-900 dark:text-white">
                            {{ $activityLog->created_at->format('Y-m-d H:i:s') }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $activityLog->created_at->diffForHumans() }}
                        </p>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                    <p class="text-sm text-gray-900 dark:text-white">{{ $activityLog->description }}</p>
                </div>
            </div>
        </div>

        <!-- Old Values -->
        @if ($activityLog->old_values && !empty($activityLog->old_values))
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-red-50 dark:bg-red-900/20">
                    <h2 class="text-lg font-semibold text-red-900 dark:text-red-300">Old Values</h2>
                </div>
                <div class="px-6 py-4">
                    <pre class="bg-gray-100 dark:bg-gray-900 p-4 rounded-lg overflow-x-auto text-sm">{{ json_encode($activityLog->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                </div>
            </div>
        @endif

        <!-- New Values -->
        @if ($activityLog->new_values && !empty($activityLog->new_values))
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-green-50 dark:bg-green-900/20">
                    <h2 class="text-lg font-semibold text-green-900 dark:text-green-300">New Values</h2>
                </div>
                <div class="px-6 py-4">
                    <pre class="bg-gray-100 dark:bg-gray-900 p-4 rounded-lg overflow-x-auto text-sm">{{ json_encode($activityLog->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                </div>
            </div>
        @endif
    </div>
@endsection
