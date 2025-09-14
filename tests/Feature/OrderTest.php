<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class OrderTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        Artisan::call('migrate:fresh', ['--seed' => true]);
    }

    public function test_can_list_orders()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);
        $order->products()->attach($product->id, ['quantity' => 1, 'price' => $product->price]);

        $response = $this->actingAs($user, 'api')->getJson('/api/v1/orders');

        $response->assertStatus(200)
            ->assertJsonCount(1);
    }

    public function test_can_create_order()
    {
            $user = User::factory()->create();
            $this->actingAs($user, 'api');

            $product = Product::factory()->create(['stock' => 100]);

            $orderData = [
                'products' => [
                    ['product_id' => $product->id, 'quantity' => 2]
                ]
            ];

            $response = $this->postJson('/api/v1/orders', $orderData);

            $response->assertStatus(202);
            $this->assertDatabaseHas('orders', [
                'user_id' => $user->id,
            ]);
    }

    public function test_can_show_order()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'api')->getJson("/api/v1/orders/{$order->id}");

        $response->assertStatus(201)
                 ->assertJson(['id' => $order->id]);
    }

    
}