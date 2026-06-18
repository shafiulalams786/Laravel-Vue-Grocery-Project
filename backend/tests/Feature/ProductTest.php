<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_products(): void
    {
        $category = Category::factory()->create();
        Product::factory()->count(3)->create(['category_id' => $category->id, 'is_active' => true, 'stock' => 10]);

        $this->getJson('/api/v1/products')
             ->assertOk()
             ->assertJsonStructure(['data', 'total']);
    }

    public function test_can_get_featured_products(): void
    {
        $category = Category::factory()->create();
        Product::factory()->count(2)->featured()->create(['category_id' => $category->id, 'is_active' => true, 'stock' => 10]);

        $this->getJson('/api/v1/products/featured')
             ->assertOk();
    }

    public function test_can_search_products(): void
    {
        $category = Category::factory()->create();
        Product::factory()->create(['category_id' => $category->id, 'name' => 'Organic Apple', 'is_active' => true, 'stock' => 10]);

        $this->getJson('/api/v1/products/search?q=Apple')
             ->assertOk();
    }

    public function test_can_get_single_product(): void
    {
        $category = Category::factory()->create();
        $product  = Product::factory()->create(['category_id' => $category->id, 'is_active' => true]);

        $this->getJson("/api/v1/products/{$product->slug}")
             ->assertOk()
             ->assertJsonPath('product.id', $product->id);
    }

    public function test_inactive_products_are_excluded(): void
    {
        $category = Category::factory()->create();
        Product::factory()->create(['category_id' => $category->id, 'is_active' => false]);

        $response = $this->getJson('/api/v1/products')->assertOk();
        $this->assertEquals(0, $response->json('total'));
    }
}
