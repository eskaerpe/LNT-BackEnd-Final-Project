<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\JsonResponse;

class PublicCategoryController extends Controller
{
    /**
     * List all categories (public, read-only).
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
     * Show a specific category by ID (public, read-only).
     */
    public function show(Category $category): JsonResponse
    {
        $category->load('products');

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
                'created_at' => $category->created_at,
                'updated_at' => $category->updated_at,
                'products' => $category->products,
            ],
        ], 200);
    }
}
