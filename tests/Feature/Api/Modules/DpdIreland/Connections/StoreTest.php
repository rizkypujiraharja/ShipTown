<?php

namespace Tests\Feature\Api\Modules\DpdIreland\Connections;

use App\User;
use Tests\TestCase;

class StoreTest extends TestCase
{
    /** @test */
    public function test_success_config_create(): void
    {
        /** @var User $user * */
        $user = User::factory()->create();
        $user->assignRole('admin');

        $response = $this->actingAs($user, 'api')->json('post', route('api.modules.dpd-ireland.connections.store'), [
            'live' => false,
            'user' => 'someuser',
            'password' => 'somepassword',
            'token' => 'sometoken',
            'contact' => 'DPD Contact',
            'contact_telephone' => '0860000000',
            'contact_email' => 'testemail@dpd.ie',
            'business_name' => 'DPD API Test Limited',
            'address_line_1' => 'Athlone Business Park',
            'address_line_2' => 'Dublin Road',
            'address_line_3' => 'Athlone',
            'address_line_4' => 'Co. Westmeath',
            'country_code' => 'IE',
        ]);

        $response->assertSuccessful();
    }

    /** @test */
    public function test_failing_config_create(): void
    {
        /** @var User $user * */
        $user = User::factory()->create();
        $user->assignRole('admin');

        $response = $this->actingAs($user, 'api')->json('post', route('api.modules.dpd-ireland.connections.store'), [
            'live' => false,
            'token' => 'sometoken',
            'contact' => 'DPD Contact',
            'contact_telephone' => '0860000000',
            'contact_email' => 'testemail@dpd.ie',
            'business_name' => 'DPD API Test Limited',
            'address_line_1' => 'Athlone Business Park',
            'address_line_2' => 'Dublin Road',
            'address_line_3' => 'Athlone',
            'address_line_4' => 'Co. Westmeath',
            'country_code' => 'IE',
        ]);

        $response->assertStatus(422);
        ray($response->json());

        $response->assertJsonValidationErrors(['user', 'password']);
    }
}
