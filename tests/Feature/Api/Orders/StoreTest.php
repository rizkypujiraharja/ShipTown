<?php

namespace Tests\Feature\Api\Orders;

use App\User;
use Laravel\Passport\Passport;
use Tests\TestCase;

class StoreTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $admin = User::factory()->create()->assignRole('admin');
        $this->actingAs($admin, 'api');
    }

    /** @test */
    public function test_store_call_returns_ok(): void
    {
        $data = [
            'order_number' => '0123456789',
            'products' => [
                [
                    'sku' => '123',
                    'name' => 'Test Product',
                    'quantity' => 2,
                    'price' => 4,
                ],

                [
                    'sku' => '456',
                    'name' => 'Test Product',
                    'quantity' => 5,
                    'price' => 10,
                ],
            ],
        ];

        $response = $this->postJson('api/orders', $data);

        ray($response->json());

        $response->assertStatus(200);

        $this->assertDatabaseHas('orders', ['order_number' => $data['order_number']]);
    }

    public function testCorrectProductsSections(): void
    {
        $data = [
            'order_number' => '001241',
            'products' => [
                [], // blank products record
            ],
        ];

        Passport::actingAs(
            User::factory()->create()
        );

        $this->postJson('api/orders', $data)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['products.0.sku'])
            ->assertJsonValidationErrors(['products.0.quantity'])
            ->assertJsonValidationErrors(['products.0.price']);
    }

    public function testIfMissingOrderNumberIsNotAllowed(): void
    {
        $data = [
            //'order_number'      => '001241',
            'products' => [
                [
                    'sku' => '123',
                    'quantity' => 2,
                    'price' => 4,
                ],

                [
                    'sku' => '456',
                    'quantity' => 5,
                    'price' => 10,
                ],
            ],
        ];

        Passport::actingAs(
            User::factory()->create()
        );

        $this->json('POST', 'api/orders', $data)
            ->assertStatus(422);
    }

    public function testIfMissingProductsSectionIsNotAllowed(): void
    {
        $data = [
            'order_number' => '001241',
        ];

        Passport::actingAs(
            User::factory()->create()
        );

        $this->json('POST', 'api/orders', $data)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['products']);
    }
}
