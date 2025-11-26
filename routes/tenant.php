<?php

declare(strict_types=1);

use App\Http\Controllers\Tenant\UserController;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByRequestData;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use App\Http\Controllers\Tenant\AuthController;

Route::middleware([
    'api',
    InitializeTenancyByRequestData::class,
])->prefix('api/tenant')
    ->group(function () {

        // Public routes
        Route::post('/auth/login', [AuthController::class, 'login']);

        // Protected routes
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/auth/logout', [AuthController::class, 'logout']);
            Route::get('/auth/me', [AuthController::class, 'me']);


            // Users
            Route::prefix('users')->group(function () {
                Route::get('/', [UserController::class, 'index'])
                    ->middleware('permission.tenant:tenant.users.view');

                Route::get('/{user}', [UserController::class, 'show'])
                    ->middleware('permission.tenant:tenant.users.view');

                Route::post('/', [UserController::class, 'store'])
                    ->middleware('permission.tenant:tenant.users.create');

                Route::put('/{user}', [UserController::class, 'update'])
                    ->middleware('permission.tenant:tenant.users.update');

                Route::delete('/{user}', [UserController::class, 'destroy'])
                    ->middleware('permission.tenant:tenant.users.delete');
            });
        });
    });
