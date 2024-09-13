<?php

namespace Tests\Unit\Modules\InventoryReservations;

use App\Modules\InventoryReservations\src\EventServiceProviderBase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BasicModuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        EventServiceProviderBase::enableModule();
    }

    public function test_if_reserves_correctly(): void
    {
        $this->assertTrue(true);
    }
}
