<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestCartTest extends TestCase
{
    use RefreshDatabase;

    private function getSession(): string
    {
        return $this->postJson('/api/v1/guest/session')
                    ->assertOk()
                    ->json('session_id');
    }

    public function test_guest_can_create_session(): void
    {
        $this->postJson('/api/v1/guest/session')
             ->assertOk()
             ->assertJsonStructure(['session_id']);
    }

    public function test_guest_can_add_item_to_cart(): void
    {
        $category = Category::factory()->create();
        $product  = Product::factory()->create(['category_id' => $category->id, 'stock' => 10, 'is_active' => true]);
        $session  = $this->getSession();

        $this->postJson("/api/v1/guest/cart/{$session}", [
            'product_id' => $product->id,
            'quantity'   => 2,
        ])->assertOk()->assertJsonStructure(['item']);
    }

    public function test_guest_can_view_cart(): void
    {
        $session = $this->getSession();

        $this->getJson("/api/v1/guest/cart/{$session}")
             ->assertOk()
             ->assertJsonStructure(['items', 'summary']);
    }

    public function test_guest_can_remove_item(): void
    {
        $category = Category::factory()->create();
        $product  = Product::factory()->create(['category_id' => $category->id, 'stock' => 10, 'is_active' => true]);
        $session  = $this->getSession();

        $item = $this->postJson("/api/v1/guest/cart/{$session}", [
            'product_id' => $product->id,
            'quantity'   => 1,
        ])->json('item');

        $this->deleteJson("/api/v1/guest/cart/{$session}/{$item['id']}")
             ->assertOk();
    }

    public function test_cannot_add_out_of_stock_product(): void
    {
        $category = Category::factory()->create();
        $product  = Product::factory()->outOfStock()->create(['category_id' => $category->id, 'is_active' => true]);
        $session  = $this->getSession();

        $this->postJson("/api/v1/guest/cart/{$session}", [
            'product_id' => $product->id,
            'quantity'   => 1,
        ])->assertStatus(422);
    }
}
