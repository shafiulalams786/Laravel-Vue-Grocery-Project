<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductAndCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Fresh Fruits', 'slug' => 'fresh-fruits', 'sort_order' => 1],
            ['name' => 'Vegetables', 'slug' => 'vegetables', 'sort_order' => 2],
            ['name' => 'Dairy & Eggs', 'slug' => 'dairy-eggs', 'sort_order' => 3],
            ['name' => 'Meat & Fish', 'slug' => 'meat-fish', 'sort_order' => 4],
            ['name' => 'Bakery', 'slug' => 'bakery', 'sort_order' => 5],
            ['name' => 'Beverages', 'slug' => 'beverages', 'sort_order' => 6],
            ['name' => 'Snacks', 'slug' => 'snacks', 'sort_order' => 7],
            ['name' => 'Pantry', 'slug' => 'pantry', 'sort_order' => 8],
        ];

        foreach ($categories as $cat) {
            Category::create($cat);
        }

        $products = [
            // Fruits
            ['category' => 'fresh-fruits', 'name' => 'Organic Apples', 'price' => 3.99, 'unit' => 'lb', 'stock' => 100, 'is_featured' => true, 'sale_price' => 2.99],
            ['category' => 'fresh-fruits', 'name' => 'Ripe Bananas', 'price' => 1.49, 'unit' => 'bunch', 'stock' => 80, 'is_featured' => true],
            ['category' => 'fresh-fruits', 'name' => 'Strawberries', 'price' => 4.99, 'unit' => 'pint', 'stock' => 60, 'is_featured' => true, 'sale_price' => 3.49],
            ['category' => 'fresh-fruits', 'name' => 'Navel Oranges', 'price' => 5.99, 'unit' => 'bag', 'stock' => 50],
            ['category' => 'fresh-fruits', 'name' => 'Grapes Seedless', 'price' => 3.49, 'unit' => 'lb', 'stock' => 70],
            ['category' => 'fresh-fruits', 'name' => 'Watermelon', 'price' => 7.99, 'unit' => 'each', 'stock' => 30],
            ['category' => 'fresh-fruits', 'name' => 'Mango', 'price' => 1.99, 'unit' => 'each', 'stock' => 90, 'is_featured' => true],
            ['category' => 'fresh-fruits', 'name' => 'Blueberries', 'price' => 5.49, 'unit' => 'pint', 'stock' => 55],

            // Vegetables
            ['category' => 'vegetables', 'name' => 'Baby Spinach', 'price' => 3.99, 'unit' => 'bag', 'stock' => 75, 'is_featured' => true],
            ['category' => 'vegetables', 'name' => 'Cherry Tomatoes', 'price' => 2.99, 'unit' => 'pint', 'stock' => 80],
            ['category' => 'vegetables', 'name' => 'Broccoli', 'price' => 2.49, 'unit' => 'head', 'stock' => 60],
            ['category' => 'vegetables', 'name' => 'Carrots Organic', 'price' => 2.99, 'unit' => 'lb', 'stock' => 100],
            ['category' => 'vegetables', 'name' => 'Bell Peppers Mix', 'price' => 4.49, 'unit' => 'bag', 'stock' => 65, 'sale_price' => 3.99],
            ['category' => 'vegetables', 'name' => 'Avocado', 'price' => 1.49, 'unit' => 'each', 'stock' => 120, 'is_featured' => true],
            ['category' => 'vegetables', 'name' => 'Sweet Corn', 'price' => 0.79, 'unit' => 'each', 'stock' => 150],
            ['category' => 'vegetables', 'name' => 'Cucumber', 'price' => 1.29, 'unit' => 'each', 'stock' => 90],

            // Dairy
            ['category' => 'dairy-eggs', 'name' => 'Whole Milk', 'price' => 3.99, 'unit' => 'gallon', 'stock' => 50, 'is_featured' => true],
            ['category' => 'dairy-eggs', 'name' => 'Free-Range Eggs', 'price' => 5.99, 'unit' => 'dozen', 'stock' => 80, 'is_featured' => true],
            ['category' => 'dairy-eggs', 'name' => 'Greek Yogurt', 'price' => 4.49, 'unit' => 'each', 'stock' => 60],
            ['category' => 'dairy-eggs', 'name' => 'Cheddar Cheese', 'price' => 5.99, 'unit' => 'block', 'stock' => 45],
            ['category' => 'dairy-eggs', 'name' => 'Butter Unsalted', 'price' => 4.99, 'unit' => 'lb', 'stock' => 40],

            // Meat
            ['category' => 'meat-fish', 'name' => 'Chicken Breast', 'price' => 8.99, 'unit' => 'lb', 'stock' => 40, 'is_featured' => true],
            ['category' => 'meat-fish', 'name' => 'Salmon Fillet', 'price' => 12.99, 'unit' => 'lb', 'stock' => 30, 'sale_price' => 10.99],
            ['category' => 'meat-fish', 'name' => 'Ground Beef', 'price' => 7.99, 'unit' => 'lb', 'stock' => 50],

            // Bakery
            ['category' => 'bakery', 'name' => 'Sourdough Bread', 'price' => 5.99, 'unit' => 'loaf', 'stock' => 30, 'is_featured' => true],
            ['category' => 'bakery', 'name' => 'Croissants', 'price' => 3.99, 'unit' => 'pack', 'stock' => 40, 'sale_price' => 2.99],
            ['category' => 'bakery', 'name' => 'Bagels Everything', 'price' => 4.49, 'unit' => 'pack', 'stock' => 35],

            // Beverages
            ['category' => 'beverages', 'name' => 'Orange Juice', 'price' => 4.99, 'unit' => 'carton', 'stock' => 60, 'is_featured' => true],
            ['category' => 'beverages', 'name' => 'Sparkling Water', 'price' => 5.99, 'unit' => 'pack', 'stock' => 80],
            ['category' => 'beverages', 'name' => 'Cold Brew Coffee', 'price' => 6.99, 'unit' => 'bottle', 'stock' => 45, 'sale_price' => 5.49],

            // Pantry
            ['category' => 'pantry', 'name' => 'Olive Oil Extra Virgin', 'price' => 9.99, 'unit' => 'bottle', 'stock' => 50],
            ['category' => 'pantry', 'name' => 'Pasta Spaghetti', 'price' => 2.49, 'unit' => 'box', 'stock' => 100],
            ['category' => 'pantry', 'name' => 'Canned Tomatoes', 'price' => 1.99, 'unit' => 'can', 'stock' => 120],
        ];

        $descriptions = [
            'Fresh from local farms, picked at peak ripeness.',
            'Premium quality, sustainably sourced.',
            'Organically grown, no pesticides or herbicides.',
            'Farm-fresh, delivered daily.',
            'Hand-selected for quality and freshness.',
        ];

        foreach ($products as $prod) {
            $category = Category::where('slug', $prod['category'])->first();
            Product::create([
                'category_id' => $category->id,
                'name' => $prod['name'],
                'slug' => Str::slug($prod['name']),
                'description' => $descriptions[array_rand($descriptions)],
                'price' => $prod['price'],
                'sale_price' => $prod['sale_price'] ?? null,
                'stock' => $prod['stock'],
                'unit' => $prod['unit'],
                'is_featured' => $prod['is_featured'] ?? false,
                'is_active' => true,
            ]);
        }
    }
}
