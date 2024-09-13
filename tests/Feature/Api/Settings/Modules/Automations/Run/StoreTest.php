<?php

namespace Tests\Feature\Api\Settings\Modules\Automations\Run;

use App\Modules\Automations\src\Models\Automation;
use App\User;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StoreTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $admin = User::factory()->create()->assignRole(Role::findOrCreate('user', 'api'));
        $this->actingAs($admin, 'api');
    }

    /** @test */
    public function test_store_call_returns_ok(): void
    {
        $automation = Automation::factory()->create();

        $response = $this->post(route('settings.modules.automations.run.store'), [
            'automation_id' => $automation->getKey(),
        ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'automation_id',
                'time',
            ],
        ]);
    }
}
