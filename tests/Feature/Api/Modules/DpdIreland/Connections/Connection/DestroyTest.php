<?php

namespace Tests\Feature\Api\Modules\DpdIreland\Connections\Connection;

use App\Modules\DpdIreland\src\Models\DpdIreland;
use App\User;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DestroyTest extends TestCase
{
    /** @test */
    public function test_delete_config(): void
    {
        $connection = DpdIreland::query()->create([
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
        $user->assignRole(Role::findOrCreate('admin', 'api'));
        $this->actingAs($user, 'api');

        $response = $this->delete(route('api.modules.dpd-ireland.connections.destroy', [
            'connection' => $connection->getKey(),
        ]));

        ray($response->json());

        $response->assertOk();

        $this->assertEquals(0, DpdIreland::count());
    }
}
