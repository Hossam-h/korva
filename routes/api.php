<?php

use App\Http\Controllers\Api\Admin\AcademyController;
use App\Http\Controllers\Api\Admin\AdminController;
use App\Http\Controllers\Api\Admin\AuthController;
use App\Http\Controllers\Api\Admin\RolePermissionController;
use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => 'api',
    'prefix' => 'admin',
], function ($router) {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:admin');
    Route::post('refresh', [AuthController::class, 'refresh'])->middleware('auth:admin');
    Route::post('me', [AuthController::class, 'me'])->middleware('auth:admin');

    // Role & Permission Management (all require auth:admin)
    Route::middleware('auth:admin')->group(function () {
        // Roles
        Route::get('roles', [RolePermissionController::class, 'allRoles']);
        Route::post('roles', [RolePermissionController::class, 'createRole']);
        Route::get('roles/{id}', [RolePermissionController::class, 'roleWithPermissions']);
        Route::post('roles/{id}/assign-permissions', [RolePermissionController::class, 'assignPermissionsToRole']);
        Route::post('roles/{id}/remove-permissions', [RolePermissionController::class, 'removePermissionsFromRole']);

        // Permissions
        Route::get('permissions', [RolePermissionController::class, 'allPermissions']);
        Route::post('permissions', [RolePermissionController::class, 'createPermission']);

        // Admin management
        Route::post('admins', [AdminController::class, 'store']);
        Route::post('admins/{admin}/assign-role', [RolePermissionController::class, 'assignRole']);
        Route::post('admins/{admin}/remove-role', [RolePermissionController::class, 'removeRole']);

        // Academy management
        Route::get('academies/{academy}', [AcademyController::class, 'show']);
        Route::put('academies/{academy}', [AcademyController::class, 'update']);
        Route::patch('academies/{academy}/status', [AcademyController::class, 'changeStatus']);
    });
});
