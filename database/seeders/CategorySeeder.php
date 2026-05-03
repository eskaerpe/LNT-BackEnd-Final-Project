<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 5 seed categories with specific names
        $categories = [
            [
                'name' => 'Electronics',
                'slug' => 'electronics',
                'description' => 'Electronic devices and gadgets',
            ],
            [
                'name' => 'Books',
                'slug' => 'books',
                'description' => 'Books and reading materials',
            ],
            [
                'name' => 'Clothing',
                'slug' => 'clothing',
                'description' => 'Apparel and fashion items',
            ],
            [
                'name' => 'Home & Garden',
                'slug' => 'home-garden',
                'description' => 'Home and garden products',
            ],
            [
                'name' => 'Sports & Outdoors',
                'slug' => 'sports-outdoors',
                'description' => 'Sports and outdoor equipment',
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
