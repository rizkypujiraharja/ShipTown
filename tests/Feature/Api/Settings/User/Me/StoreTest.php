<?php

namespace Tests\Feature\Api\Settings\User\Me;

use App\User;
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
        $params = [
            'name' => 'User Test',
            'printer_id' => 1,
            'ask_for_shipping_number' => false,
        ];

        $response = $this->post(route('me.store', $params));
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'email',
                'location_id',
                'printer_id',
                'address_label_template',
                'roles' => [
                    '*' => [
                        'id',
                        'name',
                        'guard_name',
                    ],
                ],
            ],
        ]);
    }
}
