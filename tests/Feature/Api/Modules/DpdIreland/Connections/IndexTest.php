<?php

namespace Tests\Feature\Api\Modules\DpdIreland\Connections;

use App\Modules\DpdIreland\src\Models\DpdIreland;
use App\User;
use Tests\TestCase;

class IndexTest extends TestCase
{
    /** @test */
    public function test_index_call_with_config(): void
    {
        DpdIreland::query()->create([
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

        /** @var User $user * */
        $user = User::factory()->create();
        $user->assignRole('admin');

        $response = $this->actingAs($user, 'api')->get(route('api.modules.dpd-ireland.connections.index'));
        $response->assertOk();
    }
}
