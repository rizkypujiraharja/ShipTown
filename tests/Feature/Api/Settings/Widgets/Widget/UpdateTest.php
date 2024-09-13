<?php

namespace Tests\Feature\Api\Settings\Widgets\Widget;

use App\Models\Widget;
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

    /** @test */
    public function test_store_call_returns_ok(): void
    {
        $widget = Widget::create(['name' => 'testing', 'config' => []]);

        $response = $this->put(route('widgets.update', $widget), [
            'name' => 'Tes widget',
            'config' => [],
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'name',
                'config' => [],
                'id',
            ],
        ]);
    }
}
