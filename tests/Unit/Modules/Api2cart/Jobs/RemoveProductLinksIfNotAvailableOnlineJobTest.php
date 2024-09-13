<?php

namespace Tests\Unit\Modules\Api2cart\Jobs;

use App\Models\Product;
use App\Modules\Api2cart\src\Jobs\RemoveProductLinksIfNotAvailableOnlineJob;
use App\Modules\Api2cart\src\Models\Api2cartProductLink;
use Tests\TestCase;

class RemoveProductLinksIfNotAvailableOnlineJobTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function testExample(): void
    {
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        Api2cartProductLink::factory()->create(['product_id' => $product1->getKey()]);
        Api2cartProductLink::factory()->create(['product_id' => $product2->getKey()]);

        $product2->attachTag('Available Online');

        RemoveProductLinksIfNotAvailableOnlineJob::dispatch();

        $this->assertEquals(1, Api2cartProductLink::query()->count('id'));
    }
}
