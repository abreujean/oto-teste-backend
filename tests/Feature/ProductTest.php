<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_products()
    {
        $user = User::factory()->create();
        Product::factory()->count(3)->create();

        $response = $this->actingAs($user, 'api')->getJson('/api/v1/products');

        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    public function test_can_create_product()
    {
        $user = User::factory()->create();
        $productData = [
            'name' => 'New Product',
            'description' => 'This is a new product.',
            'price' => 99.99,
            'stock' => 10
        ];

        $response = $this->actingAs($user, 'api')->postJson('/api/v1/products', $productData);

        $response->assertStatus(201);
        $this->assertDatabaseHas('products', ['name' => 'New Product']);
    }

    public function test_can_show_product()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $response = $this->actingAs($user, 'api')->getJson("/api/v1/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $product->id]);
    }

    public function test_can_update_product()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $updateData = ['name' => 'Updated Product Name'];

        $response = $this->actingAs($user, 'api')->putJson("/api/v1/products/{$product->id}", $updateData);

        $response->assertStatus(201);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'name' => 'Updated Product Name']);
    }

    public function test_can_delete_product()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $response = $this->actingAs($user, 'api')->deleteJson("/api/v1/products/{$product->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }
}