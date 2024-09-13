<?php

namespace Tests\Feature\Api\Transactions\Transaction;

use App\Models\DataCollection;
use App\Models\OrderAddress;
use App\User;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    private string $uri = '/api/transactions/';

    /** @test */
    public function testIfCallReturnsOk(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole(Role::findOrCreate('admin', 'api'));

        /** @var DataCollection $dataCollectionToUpdate */
        $dataCollectionToUpdate = DataCollection::factory()->create([
            'name' => 'Test Transaction',
            'type' => \App\Models\DataCollectionTransaction::class,
        ]);

        /** @var OrderAddress $shippingAddress */
        $shippingAddress = OrderAddress::factory()->create(['country_name' => 'Ireland', 'country_code' => 'IE']);
        /** @var OrderAddress $billingAddress */
        $billingAddress = OrderAddress::factory()->create(['country_name' => 'Ireland', 'country_code' => 'IE']);

        $response = $this->actingAs($user, 'api')->putJson($this->uri . $dataCollectionToUpdate->id, [
            'shipping_address_id' => $shippingAddress->id,
            'billing_address_id' => $billingAddress->id,
        ]);

        ray($response->json());

        $response->assertOk();

        $this->assertDatabaseHas(
            'data_collections',
            [
                'id' => $dataCollectionToUpdate->id,
                'name' => 'Test Transaction'
            ]
        );
    }
}
