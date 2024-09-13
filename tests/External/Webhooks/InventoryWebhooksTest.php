<?php

namespace Tests\External\Webhooks;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\Warehouse;
use App\Modules\Webhooks\src\Jobs\PublishInventoryWebhooksJob;
use App\Modules\Webhooks\src\WebhooksServiceProviderBase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class InventoryWebhooksTest extends TestCase
{
    /** @test */
    public function test_module_basic_functionality(): void
    {
        WebhooksServiceProviderBase::enableModule();

        Bus::fake();

        Product::factory()->create();
        Warehouse::factory()->create();

        $inventory = Inventory::where([
            'product_id' => Product::first()->getKey(),
            'warehouse_id' => Warehouse::first()->getKey(),
        ])->first();

        $inventory->update(['quantity' => $inventory->quantity + 1]);

        Bus::assertDispatchedAfterResponse(PublishInventoryWebhooksJob::class);
    }
}
