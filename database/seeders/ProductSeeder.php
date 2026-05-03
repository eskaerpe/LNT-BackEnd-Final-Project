<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get or create categories
        $categories = Category::all();

        // If no categories exist, create them
        if ($categories->isEmpty()) {
            $this->call(CategorySeeder::class);
            $categories = Category::all();
        }

        // Create 20 products, 4 per category
        foreach ($categories as $category) {
            Product::factory(4)->create([
                'category_id' => $category->id,
            ]);
        }
    }
}
