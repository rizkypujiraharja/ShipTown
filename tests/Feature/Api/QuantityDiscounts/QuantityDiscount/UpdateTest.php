<?php

namespace Tests\Feature\Api\QuantityDiscounts\QuantityDiscount;

use App\Modules\DataCollectorQuantityDiscounts\src\Models\QuantityDiscount;
use App\User;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    private string $uri = 'api/quantity-discounts/';

    /** @test */
    public function testIfCallReturnsOk(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole(Role::findOrCreate('admin', 'api'));

        /** @var QuantityDiscount $discountToUpdate */
        $discountToUpdate = QuantityDiscount::factory()->create();

        $response = $this->actingAs($user, 'api')->putJson($this->uri.$discountToUpdate->id, [
            'name' => 'Test Discount',
            'job_class' => 'App\\Modules\\DataCollectorQuantityDiscounts\\src\\Jobs\\BuyXGetYForZPriceDiscount',
            'configuration' => [
                'quantity_full_price' => 10,
                'quantity_discounted' => 5,
                'discounted_price' => 1,
            ],
        ]);

        ray($response->json());

        $response->assertOk();

        $this->assertDatabaseHas(
            'modules_quantity_discounts',
            [
                'id' => $discountToUpdate->id,
                'name' => 'Test Discount',
            ]
        );
    }
}
