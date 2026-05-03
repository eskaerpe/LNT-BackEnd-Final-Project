<?php

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

// Authentication routes (Phase 1 - placeholder)
// POST /api/login - User login (JWT)
// POST /api/logout - User logout

// Public API routes (Phase 2 - placeholder)
// GET /api/categories - List all categories (public, paginated)
// GET /api/products - List all products (public, paginated)
// GET /api/products/{slug} - Get product by slug (public)

// Admin API routes (Phase 3 - placeholder, all require auth:api)
// POST /api/admin/categories - Create category
// GET /api/admin/categories - List categories
// PUT /api/admin/categories/{id} - Update category
// DELETE /api/admin/categories/{id} - Delete category
// POST /api/admin/products - Create product
// GET /api/admin/products - List products
// PUT /api/admin/products/{id} - Update product
// DELETE /api/admin/products/{id} - Delete product
// POST /api/admin/users - Create user
// GET /api/admin/users - List users
// PUT /api/admin/users/{id} - Update user
// DELETE /api/admin/users/{id} - Delete user (with validation)
