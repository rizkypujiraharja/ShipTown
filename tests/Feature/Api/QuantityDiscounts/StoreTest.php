<?php

namespace Tests\Feature\Api\QuantityDiscounts;

use App\User;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StoreTest extends TestCase
{
    private string $uri = 'api/quantity-discounts/';

    /** @test */
    public function testIfCallReturnsOk(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole(Role::findOrCreate('admin', 'api'));

        $response = $this->actingAs($user, 'api')->postJson($this->uri, [
            'name' => 'Test Discount',
            'type' => 'App\\Modules\\QuantityDiscounts\\src\\Jobs\\CalculateSoldPriceForBuyXGetYForZPriceDiscount',
        ]);

        ray($response->json());

        $response->assertSuccessful();

        $this->assertDatabaseHas('modules_quantity_discounts', ['id' => $response->json('data.id')]);
    }
}
