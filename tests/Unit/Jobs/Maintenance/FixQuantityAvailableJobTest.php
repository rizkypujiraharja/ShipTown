<?php

namespace Tests\Unit\Jobs\Maintenance;

use App\Models\Product;
use App\Modules\Maintenance\src\Jobs\Products\FixQuantityAvailableJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FixQuantityAvailableJobTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function testExample(): void
    {
        Product::factory()->count(10)->create();

        Product::query()->update([
            'quantity_available' => 2,
        ]);

        FixQuantityAvailableJob::dispatch();

        $this->assertDatabaseMissing('products', [
            'quantity_available' => 2,
        ]);
    }
}
