<?php

namespace Tests\Feature\Api\Configurations;

use App\Models\Configuration;
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
    public function test_bulkupdate_call_returns_ok(): void
    {
        Configuration::create([
            'business_name' => 'Some name',
        ]);

        $response = $this->post(route('api.configurations.store'), [
            'business_name' => 'new name',
        ]);
        $response->assertOk();

        $config = Configuration::first();
        $this->assertEquals($config->business_name, 'new name');
    }
}
