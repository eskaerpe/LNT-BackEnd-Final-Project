<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    /**
     * List all categories (admin).
     */
    public function index(): JsonResponse
    {
        $categories = Category::with('products')->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $categories,
        ], 200);
    }

    /**
     * Store a new category.
     */
    public function store(StoreCategoryRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $category = Category::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Category created successfully.',
                'data' => $category,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create category.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show a specific category.
     */
    public function show(Category $category): JsonResponse
    {
        $category->load('products');

        return response()->json([
            'success' => true,
            'data' => $category,
        ], 200);
    }

    /**
     * Update a category.
     */
    public function update(UpdateCategoryRequest $request, Category $category): JsonResponse
    {
        try {
            $validated = $request->validated();

            $category->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Category updated successfully.',
                'data' => $category,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update category.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a category.
     * Returns 409 Conflict if products are assigned to this category (ON DELETE RESTRICT).
     */
    public function destroy(Category $category): JsonResponse
    {
        try {
            // Check if category has products
            if ($category->products()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete category with assigned products.',
                    'errors' => [
                        'category' => 'This category has ' . $category->products()->count() . ' product(s). Please reassign or delete them first.',
                    ]
                ], 409);
            }

            $category->delete();

            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete category.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
