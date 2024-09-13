<?php

namespace Tests\Unit\Modules\Maintenance\Jobs;

use App\Models\Order;
use App\Modules\AutoPilot\src\Jobs\ClearPackerIdJob;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class ClearOrderPackerAssignmentJobTest extends TestCase
{
    public function testIfDoesNotUpdateLessThan12HoursOfInactivity(): void
    {
        // prepare
        Order::query()->forceDelete();

        $user = User::factory()->create();

        Order::factory()->create([
            'packed_at' => null,
            'packer_user_id' => $user->getKey(),
            'updated_at' => Carbon::now()->subHours(11),
        ]);

        // act
        Bus::fake();

        ClearPackerIdJob::dispatchSync();

        // assert
        $this->assertFalse(
            Order::whereNull('packer_user_id')
                ->exists()
        );
    }

    /**
     * A basic feature test.
     */
    public function testIfClearsPackersAfter12hoursInactivity(): void
    {
        Order::query()->forceDelete();

        $user = User::factory()->create();

        $this->actingAs($user);

        Order::factory()->create([
            'packed_at' => null,
            'packer_user_id' => $user->getKey(),
            'updated_at' => Carbon::now()->subHours(14),
        ]);

        ClearPackerIdJob::dispatchSync();

        $this->assertTrue(
            Order::whereNull('packer_user_id')
                ->exists()
        );
    }
}
