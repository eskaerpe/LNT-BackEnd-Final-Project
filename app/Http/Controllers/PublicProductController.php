<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicProductController extends Controller
{
    /**
     * List all products (public, read-only, paginated with filters).
     * Query parameters:
     *   - page: pagination page number (default 1)
     *   - per_page: items per page (default 10)
     *   - category: filter by category slug
     *   - search: search by product name
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::with('category');

        // Filter by category slug
        if ($request->has('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->get('category'));
            });
        }

        // Search by product name
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where('name', 'like', "%{$search}%");
        }

        $perPage = $request->get('per_page', 10);
        $products = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $products,
        ], 200);
    }

    /**
     * Show a specific product by slug (public, read-only).
     */
    public function show(Request $request, $slug): JsonResponse
    {
        $product = Product::where('slug', $slug)->with('category')->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $product,
        ], 200);
    }
}
