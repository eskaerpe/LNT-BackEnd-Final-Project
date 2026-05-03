<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

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

            // Handle file upload for image_url
            if ($request->hasFile('image_url')) {
                $file = $request->file('image_url');
                $path = $file->store('products', 'public');
                $validated['image_url'] = '/storage/' . $path; // Web-accessible URL
            }

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

            // Handle file upload for image_url
            if ($request->hasFile('image_url')) {
                // Delete old image if it was a file (stored in storage/app/public)
                if ($product->image_url && str_starts_with($product->image_url, '/storage/')) {
                    $oldPath = str_replace('/storage/', '', $product->image_url);
                    Storage::disk('public')->delete($oldPath);
                }

                $file = $request->file('image_url');
                $path = $file->store('products', 'public');
                $validated['image_url'] = '/storage/' . $path; // Web-accessible URL
            }

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
