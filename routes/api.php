<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Central\AuthController;
use App\Http\Controllers\Central\TenantController;
use App\Http\Controllers\Central\UserController;

// Public routes
Route::post('/auth/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::get('/auth/tenants', [AuthController::class, 'tenants']);

    // Central resources

    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index'])
            ->middleware('permission.central:central.users.view');
        Route::post('/', [UserController::class, 'store'])
            ->middleware('permission.central:central.users.create');
        Route::get('/{user}', [UserController::class, 'show'])
            ->middleware('permission.central:central.users.view');
        Route::put('/{user}', [UserController::class, 'update'])
            ->middleware('permission.central:central.users.update');
        Route::delete('/{user}', [UserController::class, 'destroy'])
            ->middleware('permission.central:central.users.delete');
    });

    Route::prefix('tenants')->group(function () {
        Route::get('/', [TenantController::class, 'index'])
            ->middleware('permission.central:central.tenants.view');
        Route::post('/', [TenantController::class, 'store'])
            ->middleware('permission.central:central.tenants.create');
        Route::get('/{tenant}', [TenantController::class, 'show'])
            ->middleware('permission.central:central.tenants.view');
        Route::put('/{tenant}', [TenantController::class, 'update'])
            ->middleware('permission.central:tenants.update');
        Route::delete('/{tenant}', [TenantController::class, 'destroy'])
            ->middleware('permission.central:tenants.delete');
    });
});
