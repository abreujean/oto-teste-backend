<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_users(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'api');
        User::factory()->create();
        $response = $this->getJson('/api/v1/users');

        $response->assertStatus(200);
    }

    public function test_can_create_user(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'api');
        $userData = [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/v1/users', $userData);

        $response->assertStatus(201)
                 ->assertJson(['message' => 'Usuario ' . $userData['name'] . ' criado com sucesso!']);
    }

    public function test_can_show_user(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'api');
        $userToShow = User::factory()->create();
        $response = $this->getJson("/api/v1/users/{$userToShow->id}");

        $response->assertStatus(201)
                 ->assertJson(['id' => $userToShow->id]);
    }

    public function test_can_update_user(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'api');
        $userToUpdate = User::factory()->create();
        $updateData = ['name' => 'Updated Name'];

        $response = $this->putJson("/api/v1/users/{$userToUpdate->id}", $updateData);

        $response->assertStatus(200)
                 ->assertJsonFragment(['name' => 'Updated Name']);
        $this->assertDatabaseHas('users', ['id' => $userToUpdate->id, 'name' => 'Updated Name']);
    }

    public function test_can_delete_user(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'api');
        $userToDelete = User::factory()->create();
        $response = $this->deleteJson("/api/v1/users/{$userToDelete->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('users', ['id' => $userToDelete->id]);
    }

    public function test_can_list_user_orders(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'api');
        $userWithOrders = User::factory()->has(Order::factory()->count(3))->create();

        $response = $this->getJson("/api/v1/users/{$userWithOrders->id}/orders");

        $response->assertStatus(200)
                 ->assertJsonCount(3);
    }
}