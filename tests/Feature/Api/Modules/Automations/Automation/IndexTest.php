<?php

namespace Tests\Feature\Api\Modules\Automations\Automation;

use App\Events\Order\OrderCreatedEvent;
use App\Modules\Automations\src\Models\Automation;
use App\User;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class IndexTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        /** @var User $admin */
        $admin = User::factory()->create();
        $admin = $admin->assignRole(Role::findOrCreate('admin', 'api'));
        $this->actingAs($admin, 'api');
    }

    /** @test */
    public function test_show_call_returns_ok(): void
    {
        $automation = Automation::create([
            'name' => 'Store Pickup',
            'priority' => 1,
            'event_class' => OrderCreatedEvent::class,
        ]);

        $response = $this->get(route('api.modules.automations.show', $automation->getKey()));

        ray($response->json());

        $response->assertOk();

        $response->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'enabled',
                'actions',
                'conditions',
            ],
        ]);
    }
}
