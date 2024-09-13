<?php

namespace Tests\Feature\Api\Modules\Automations\Automation;

use App\Modules\Automations\src\Models\Automation;
use App\User;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $admin = User::factory()->create()->assignRole('admin');
        $this->actingAs($admin, 'api');
    }

    public function test_basic_scenarios(): void
    {
        $automation = Automation::create([
            'name' => 'Store Pickup',
            'priority' => 1,
        ]);

        $route = route('api.modules.automations.update', $automation->getKey());

        $this->put($route, [
            'name' => 'Test Automation',
            'enabled' => true,
            'priority' => rand(1, 100),
            'conditions' => [],
            'actions' => [],
        ])
            ->assertStatus(200);

        $this->assertTrue(true);
    }

    /** @test */
    public function test_update_call_returns_ok(): void
    {
        $automation = Automation::create([
            'name' => 'Store Pickup',
            'priority' => 1,
        ]);

        $data = [
            'name' => 'Test Automation',
            'enabled' => true,
            'description' => 'Some description',
            'priority' => 1,
            'conditions' => [
                [
                    'condition_class' => \App\Modules\Automations\src\Conditions\Order\CanFulfillFromLocationCondition::class,
                    'condition_value' => 'paid',
                ],
                [
                    'condition_class' => \App\Modules\Automations\src\Conditions\Order\ShippingMethodCodeEqualsCondition::class,
                    'condition_value' => 'paid',
                ],
            ],
            'actions' => [
                [
                    'priority' => 1,
                    'action_class' => \App\Modules\Automations\src\Actions\Order\SetStatusCodeAction::class,
                    'action_value' => 'store_pickup',
                ],
            ],
        ];

        $response = $this->put(route('api.modules.automations.update', $automation->id), $data);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'enabled',
            ],
        ]);
    }
}
