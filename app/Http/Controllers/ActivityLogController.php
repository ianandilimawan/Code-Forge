<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class ActivityLogController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = Auth::user();

            if (!$user) {
                abort(403);
            }

            // Administrator role has access to all actions
            if ($user->hasRole('administrator') || $user->hasRole('admin')) {
                return $next($request);
            }

            $routeName = $request->route()?->getName();
            $modelNameSnake = 'activity_log';

            if ($routeName && (str_contains($routeName, '.index') || str_contains($routeName, '.show'))) {
                abort_unless($user->hasPermission("view-{$modelNameSnake}s"), 403);
            }

            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $query = ActivityLog::with('user')->orderBy('created_at', 'desc');

        // Filter by action
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // Filter by model type
        if ($request->filled('model_type')) {
            $query->where('model_type', $request->model_type);
        }

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Search by description
        if ($request->filled('search')) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }

        $activityLogs = $query->paginate(20);

        // Get unique model types for filter
        $modelTypes = ActivityLog::select('model_type')
            ->distinct()
            ->orderBy('model_type')
            ->pluck('model_type')
            ->map(function ($type) {
                return class_basename($type);
            })
            ->unique()
            ->values();

        // Get unique actions for filter
        $actions = ActivityLog::select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        return view('admin.pages.activity_logs.index', compact('activityLogs', 'modelTypes', 'actions'));
    }

    public function show(ActivityLog $activityLog)
    {
        $activityLog->load('user');
        return view('admin.pages.activity_logs.show', compact('activityLog'));
    }
}

