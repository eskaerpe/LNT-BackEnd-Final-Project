<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    /**
     * List all products (admin).
     */
    public function index(): JsonResponse
    {
        $products = Product::with('category')->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $products,
        ], 200);
    }

    /**
     * Store a new product.
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $product = Product::create($validated);
            $product->load('category');

            return response()->json([
                'success' => true,
                'message' => 'Product created successfully.',
                'data' => $product,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create product.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show a specific product.
     */
    public function show(Product $product): JsonResponse
    {
        $product->load('category');

        return response()->json([
            'success' => true,
            'data' => $product,
        ], 200);
    }

    /**
     * Update a product.
     */
    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        try {
            $validated = $request->validated();

            $product->update($validated);
            $product->load('category');

            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully.',
                'data' => $product,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update product.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a product.
     */
    public function destroy(Product $product): JsonResponse
    {
        try {
            $product->delete();

            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete product.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
