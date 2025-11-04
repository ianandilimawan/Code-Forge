<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\ProductController;

// Public routes
Route::get('/', function () {
    return view('welcome');
});

// Admin Authentication routes
Route::prefix('admin')->name('admin.')->group(function () {
    // Public routes (login)
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    });

    // Protected admin routes
    Route::middleware('auth')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

        // Resource routes
        Route::resource('users', UserController::class);
        Route::resource('roles', RoleController::class);
        Route::resource('permissions', PermissionController::class);

        // Menu routes - custom routes must be before resource route to avoid conflicts
        Route::get('menus/icon-preview', [MenuController::class, 'iconPreview'])->name('menus.icon-preview');
        Route::post('menus/update-sort-order', [MenuController::class, 'updateSortOrder'])->name('menus.update-sort-order');
        Route::put('menus/{menu}/quick-update', [MenuController::class, 'quickUpdate'])->name('menus.quick-update');
        Route::resource('menus', MenuController::class);

        // Activity Logs routes
        Route::resource('activity_logs', ActivityLogController::class)->only(['index', 'show']);
    });
});
