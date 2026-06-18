<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        $name  = fake()->unique()->words(rand(2, 3), true);
        $price = fake()->randomFloat(2, 0.99, 29.99);

        return [
            'category_id' => Category::inRandomOrder()->first()?->id ?? Category::factory(),
            'name'        => ucwords($name),
            'slug'        => Str::slug($name) . '-' . fake()->unique()->numberBetween(1, 9999),
            'description' => fake()->sentence(12),
            'price'       => $price,
            'sale_price'  => fake()->boolean(30) ? round($price * fake()->randomFloat(2, 0.7, 0.9), 2) : null,
            'stock'       => fake()->numberBetween(0, 200),
            'unit'        => fake()->randomElement(['lb', 'kg', 'each', 'bunch', 'pack', 'bottle', 'can', 'bag']),
            'is_featured' => fake()->boolean(20),
            'is_active'   => fake()->boolean(90),
            'origin'      => fake()->country(),
        ];
    }

    public function featured(): static
    {
        return $this->state(fn ($a) => ['is_featured' => true]);
    }

    public function outOfStock(): static
    {
        return $this->state(fn ($a) => ['stock' => 0]);
    }
}
