<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ActivityLogService
{
    /**
     * Log a create action
     *
     * @param Model $model The model that was created
     * @param string|null $description Custom description
     * @return ActivityLog|null
     */
    public static function logCreate(Model $model, ?string $description = null): ?ActivityLog
    {
        return self::log('create', $model, null, $model->getAttributes(), $description);
    }

    /**
     * Log an update action
     *
     * @param Model $model The model that was updated
     * @param array|null $oldValues Old values before update
     * @param string|null $description Custom description
     * @return ActivityLog|null
     */
    public static function logUpdate(Model $model, ?array $oldValues = null, ?string $description = null): ?ActivityLog
    {
        // If oldValues not provided, get from model's original attributes
        if ($oldValues === null && $model->exists) {
            $oldValues = $model->getOriginal();
        }

        return self::log('update', $model, $oldValues, $model->getChanges(), $description);
    }

    /**
     * Log a delete action
     *
     * @param Model $model The model that was deleted
     * @param string|null $description Custom description
     * @return ActivityLog|null
     */
    public static function logDelete(Model $model, ?string $description = null): ?ActivityLog
    {
        return self::log('delete', $model, $model->getAttributes(), null, $description);
    }

    /**
     * Generic log method
     *
     * @param string $action Action type (create, update, delete)
     * @param Model $model The model being logged
     * @param array|null $oldValues Old values
     * @param array|null $newValues New values
     * @param string|null $description Custom description
     * @return ActivityLog|null
     */
    protected static function log(
        string $action,
        Model $model,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $description = null
    ): ?ActivityLog {
        try {
            // Get current user
            $user = Auth::user();
            $userId = $user ? $user->id : null;

            // Generate description if not provided
            if ($description === null) {
                $modelName = class_basename($model);
                $modelId = $model->id ?? 'new';
                $description = sprintf(
                    '%s %s %s (ID: %s)',
                    $user ? $user->name : 'System',
                    $action,
                    $modelName,
                    $modelId
                );
            }

            // Filter out sensitive fields
            $sensitiveFields = ['password', 'password_confirmation', 'remember_token', 'api_token'];

            if ($oldValues) {
                $oldValues = array_filter($oldValues, function ($key) use ($sensitiveFields) {
                    return !in_array($key, $sensitiveFields);
                }, ARRAY_FILTER_USE_KEY);
            }

            if ($newValues) {
                $newValues = array_filter($newValues, function ($key) use ($sensitiveFields) {
                    return !in_array($key, $sensitiveFields);
                }, ARRAY_FILTER_USE_KEY);
            }

            // Create activity log
            return ActivityLog::create([
                'action' => $action,
                'model_type' => get_class($model),
                'model_id' => $model->id ?? null,
                'user_id' => $userId,
                'old_values' => !empty($oldValues) ? $oldValues : null,
                'new_values' => !empty($newValues) ? $newValues : null,
                'description' => $description,
            ]);
        } catch (\Exception $e) {
            // Log error but don't break the application
            Log::error('ActivityLogService: Failed to log activity', [
                'error' => $e->getMessage(),
                'action' => $action,
                'model' => get_class($model),
            ]);
            return null;
        }
    }

    /**
     * Get logs for a specific model
     *
     * @param Model $model
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getModelLogs(Model $model, int $limit = 50)
    {
        return ActivityLog::where('model_type', get_class($model))
            ->where('model_id', $model->id)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get logs for a specific user
     *
     * @param int $userId
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getUserLogs(int $userId, int $limit = 50)
    {
        return ActivityLog::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get logs by action type
     *
     * @param string $action
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getLogsByAction(string $action, int $limit = 50)
    {
        return ActivityLog::where('action', $action)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
