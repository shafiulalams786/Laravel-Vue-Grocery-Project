<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    private function adminHeader(): array
    {
        $admin = User::factory()->admin()->create();
        $token = $admin->createToken('admin')->plainTextToken;
        return ['Authorization' => "Bearer {$token}"];
    }

    private function userHeader(): array
    {
        $user  = User::factory()->create();
        $token = $user->createToken('user')->plainTextToken;
        return ['Authorization' => "Bearer {$token}"];
    }

    public function test_admin_can_access_dashboard(): void
    {
        $this->getJson('/api/admin/dashboard/stats', $this->adminHeader())
             ->assertOk()
             ->assertJsonStructure(['revenue', 'orders', 'customers', 'products']);
    }

    public function test_non_admin_cannot_access_dashboard(): void
    {
        $this->getJson('/api/admin/dashboard/stats', $this->userHeader())
             ->assertStatus(403);
    }

    public function test_unauthenticated_cannot_access_admin(): void
    {
        $this->getJson('/api/admin/dashboard/stats')
             ->assertStatus(401);
    }

    public function test_admin_can_create_product(): void
    {
        $category = Category::factory()->create();

        $this->postJson('/api/admin/products', [
            'category_id' => $category->id,
            'name'        => 'Test Product',
            'price'       => 4.99,
            'stock'       => 50,
            'unit'        => 'each',
        ], $this->adminHeader())->assertStatus(201);
    }

    public function test_admin_can_list_orders(): void
    {
        $this->getJson('/api/admin/orders', $this->adminHeader())
             ->assertOk()
             ->assertJsonStructure(['data', 'total']);
    }

    public function test_admin_can_list_customers(): void
    {
        User::factory()->count(3)->create();

        $this->getJson('/api/admin/customers', $this->adminHeader())
             ->assertOk()
             ->assertJsonStructure(['data']);
    }

    public function test_admin_can_get_settings(): void
    {
        $this->getJson('/api/admin/settings', $this->adminHeader())
             ->assertOk();
    }
}
