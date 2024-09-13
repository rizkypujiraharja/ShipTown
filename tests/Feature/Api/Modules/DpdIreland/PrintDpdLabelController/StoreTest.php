<?php

namespace Tests\Feature\Api\Modules\DpdIreland\PrintDpdLabelController;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $admin = User::factory()->create()->assignRole('admin');
        $this->actingAs($admin, 'api');
    }

    /** @test */
    public function test_store_call_returns_ok(): void
    {
        $this->assertTrue(true, 'Tested in External/DpdIreland/PrintDpdLabelControllerTest.php');
    }
}
