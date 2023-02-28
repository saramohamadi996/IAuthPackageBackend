<?php

namespace TaFarda\IAuth\routes;

use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use TaFarda\IAuth\app\Http\Controllers\AdminAuthController;
use TaFarda\IAuth\app\Http\Controllers\AdminController;
use TaFarda\IAuth\app\Http\Controllers\LogController;
use TaFarda\IAuth\app\Http\Controllers\ProductController;
use TaFarda\IAuth\app\Http\Controllers\UserController;
use TaFarda\IAuth\app\Resources\GenericResource;
use TaFarda\IAuth\app\Resources\PermissionResource;
use TaFarda\IAuth\app\Resources\RoleResource;

Route::prefix('api/v1')->group(function () {
    // guest
    Route::middleware('guest')->group(function () {
        Route::middleware('throttle:' . config('tafarda_iauth.throttle_limit_per_minute') . ',60')
            ->group(function () {
                Route::post('admins/login', [AdminAuthController::class, 'login']);
            });
    });

//     webservice token auth
    Route::prefix('webservice')->middleware('webservice_token_auth')->group(function () {
        // user routes
        Route::prefix('users')->group(function () {
            Route::middleware('throttle:' . config('tafarda_iauth.throttle_limit_per_minute') . ',60')
            ->post('/verify-request', [UserController::class, 'verifyRequest']);
            Route::post('/verify', [UserController::class, 'verify']);
            Route::post('/show-by-value', [UserController::class, 'showByValue']);
            Route::post('/update-request', [UserController::class, 'webserviceUpdateRequest']);
            Route::put('/update', [UserController::class, 'webserviceUpdate']);
        });
    });

    // sanctum auth
    Route::middleware(['auth:sanctum'])->group(function () {

//         admin routes
        Route::prefix('admins')->group(function () {
            Route::get('/', [AdminController::class, 'index'])->middleware(['permission:admins-index']);
            Route::get('/availables', [AdminController::class, 'availables'])->middleware(['permission:admins-index']);
            Route::get('/profile', [AdminController::class, 'profile']);
            Route::post('/', [AdminController::class, 'store'])->middleware(['permission:admins-store']);
            Route::put('/{admin}', [AdminController::class, 'update'])->middleware(['permission:admins-update']);
        });

        // product routes
        Route::prefix('products')->group(function () {
            Route::get('/', [ProductController::class, 'index'])->middleware(['permission:products-index']);
            Route::post('/', [ProductController::class, 'store'])->middleware(['permission:products-store']);
            Route::put('/{product}', [ProductController::class, 'update'])->middleware(['permission:products-update']);
        });

        // user routes
        Route::prefix('users')->group(function () {
            Route::get('/', [UserController::class, 'index'])->middleware(['permission:users-index']);
            Route::post('/', [UserController::class, 'store'])->middleware(['permission:users-store']);
            Route::put('/{user}', [UserController::class, 'update'])->middleware(['permission:users-update']);
        });

        // other routes
        Route::post('admins/logout', [AdminAuthController::class, 'logout']);

        Route::middleware('role:super-admin')->get('admins/logs', [LogController::class, 'index']);

        Route::get('/roles', function () {
            return new GenericResource(['roles' => RoleResource::collection(Role::all())]);
        });

        Route::get('/permissions', function () {
            return new GenericResource(['permissions' => PermissionResource::collection(Permission::all())]);
        });

    });
});



