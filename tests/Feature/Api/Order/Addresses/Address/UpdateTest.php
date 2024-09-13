<?php

namespace Tests\Feature\Api\Order\Addresses\Address;

use App\Models\OrderAddress;
use App\User;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    /** @test */
    public function test_update_call_returns_ok(): void
    {
        $user = User::factory()->create();
        $orderAddress = OrderAddress::factory()->create();

        $response = $this->actingAs($user, 'api')->putJson(route('addresses.update', [$orderAddress]), [
            'address1' => $orderAddress->address1,
            'email' => $orderAddress->email,
            'phone' => $orderAddress->phone,
            'first_name' => $orderAddress->first_name,
            'last_name' => $orderAddress->last_name,
            'company' => $orderAddress->company,
            'address2' => $orderAddress->address2,
            'postcode' => $orderAddress->postcode,
            'city' => $orderAddress->city,
            'country_code' => $orderAddress->country_code,
            'country_name' => $orderAddress->country_name,
            'fax' => $orderAddress->fax,
            'region' => $orderAddress->region,
            'state_code' => $orderAddress->state_code,
            'state_name' => $orderAddress->state_name,
            'website' => $orderAddress->website,
        ]);

        $response->assertOk();

        $response->assertJsonStructure([
            'data' => [
                'email',
                'phone',
                'first_name',
                'last_name',
                'company',
                'address1',
                'address2',
                'postcode',
                'city',
                'country_code',
                'country_name',
                'fax',
                'region',
                'state_code',
                'state_name',
                'website',
            ],
        ]);
    }
}
