<?php

namespace Tests\Feature\Api\Modules\Automations;

use App\Modules\Automations\src\Actions\Order\SetStatusCodeAction;
use App\Modules\Automations\src\Conditions\Order\CanFulfillFromLocationCondition;
use App\Modules\Automations\src\Conditions\Order\ShippingMethodCodeEqualsCondition;
use App\User;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StoreTest extends TestCase
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
    public function test_store_call_returns_ok(): void
    {
        $data = [
            'name' => 'Test Automation',
            'enabled' => true,
            'description' => 'Some description',
            'priority' => 1,
            'conditions' => [
                [
                    'condition_class' => CanFulfillFromLocationCondition::class,
                    'condition_value' => 'paid',
                ],
                [
                    'condition_class' => ShippingMethodCodeEqualsCondition::class,
                    'condition_value' => 'paid',
                ],
            ],
            'actions' => [
                [
                    'priority' => 1,
                    'action_class' => SetStatusCodeAction::class,
                    'action_value' => 'store_pickup',
                ],
            ],
        ];
        $response = $this->post(route('api.modules.automations.store'), $data);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'enabled',
            ],
        ]);
    }
}
