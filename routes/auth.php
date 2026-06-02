<?php

use App\Http\Controllers\Api\RBAC\AuthController;
use App\Http\Controllers\Api\RBAC\RolePermissionController;
use App\Http\Controllers\Api\RBAC\UserManagementController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


// Public Authentication Routes
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

// Protected Authentication Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
});

Route::middleware(['auth:sanctum', 'role:admin'])->prefix('rbac')->group(function () {

    // ─── USER MANAGEMENT ENDPOINTS ───────────────────────────────────────
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserManagementController::class, 'index'])->name('index');
        Route::post('/', [UserManagementController::class, 'store'])->name('store');
        Route::get('{id}', [UserManagementController::class, 'show'])->name('show');
        Route::put('{id}', [UserManagementController::class, 'update'])->name('update');
        Route::delete('{id}', [UserManagementController::class, 'destroy'])->name('destroy');
    });

    // Roles Actions (CRUD)
    Route::get('/roles', [RolePermissionController::class, 'indexRoles']);
    Route::post('/roles', [RolePermissionController::class, 'storeRole']);
    Route::put('/roles/{id}', [RolePermissionController::class, 'updateRole']);
    Route::delete('/roles/{id}', [RolePermissionController::class, 'destroyRole']);

    // Permissions Actions (CRUD)
    Route::get('/permissions', [RolePermissionController::class, 'indexPermissions']);
    Route::post('/permissions', [RolePermissionController::class, 'storePermission']);
    Route::put('/permissions/{id}', [RolePermissionController::class, 'updatePermission']);
    Route::delete('/permissions/{id}', [RolePermissionController::class, 'destroyPermission']);

    // Relations & Mapping Actions
    Route::post('/roles/{id}/permissions', [RolePermissionController::class, 'syncPermissions']);
    Route::post('/assign-role', [RolePermissionController::class, 'assignRole']);
});
