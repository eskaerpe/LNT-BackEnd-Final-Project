<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PublicCategoryController;
use App\Http\Controllers\PublicProductController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Health check / Ping route
Route::get('/ping', function () {
    return response()->json(['pong' => true]);
});

// ==================== Authentication Routes ====================
Route::prefix('auth')->group(function () {
    // Public auth routes (no auth required)
    Route::post('/login', [AuthController::class, 'login'])->name('auth.login');

    // Protected auth routes (auth required)
    Route::middleware('auth:api')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::get('/me', [AuthController::class, 'me'])->name('auth.me');
    });
});

// ==================== Public API Routes ====================
Route::get('/categories', [PublicCategoryController::class, 'index'])->name('public.categories.index');
Route::get('/categories/{id}', [PublicCategoryController::class, 'show'])->name('public.categories.show');

Route::get('/products', [PublicProductController::class, 'index'])->name('public.products.index');
Route::get('/products/{slug}', [PublicProductController::class, 'show'])->name('public.products.show');

// ==================== Admin API Routes (auth:api protected) ====================
Route::middleware('auth:api')->prefix('admin')->group(function () {
    
    // Admin Categories (CRUD)
    Route::apiResource('categories', CategoryController::class);

    // Admin Products (CRUD)
    Route::apiResource('products', ProductController::class);

    // Admin Users (CRUD)
    Route::apiResource('users', UserController::class);
});
